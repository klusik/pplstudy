#!/usr/bin/env python3
"""Exercise actual HTTP authentication, CSRF and early update recovery.

Author: Rudolf Klusal
Run only against the disposable MariaDB service defined by release.yml.
"""
import fcntl
import hashlib
import http.cookiejar
import json
import re
import subprocess
import sys
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path
from zipfile import ZipFile

BASE = "http://127.0.0.1:8765"
PASSWORD = "disposable-ci-test-password-42"


class NoRedirect(urllib.request.HTTPRedirectHandler):
    """Expose redirect status and Location rather than following them."""
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


def browser():
    """Create an isolated browser cookie jar."""
    return urllib.request.build_opener(NoRedirect(), urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))


def request(client, route, fields=None):
    """Issue one HTTP request with an optional form and capture status/body."""
    body = None if fields is None else urllib.parse.urlencode(fields).encode()
    req = urllib.request.Request(BASE + route, data=body)
    try:
        response = client.open(req, timeout=10)
    except urllib.error.HTTPError as error:
        response = error
    with response:
        return response.status, response.headers, response.read().decode("utf-8", errors="replace")


def require(condition, message):
    """Fail the CI job with a descriptive assertion."""
    if not condition:
        raise AssertionError(message)


def csrf(html):
    """Extract the server-rendered CSRF value."""
    match = re.search(r'name="csrf_token" value="([0-9a-f]{64})"', html)
    require(match is not None, "Missing CSRF token")
    return match.group(1)


def login(username):
    """Log in using the actual user_login controller and database."""
    client = browser()
    status, _, html = request(client, "/login")
    require(status == 200, f"Login form status: {status}")
    status, headers, _ = request(client, "/login", {
        "username": username, "password": PASSWORD, "csrf_token": csrf(html),
    })
    require(status == 302, f"Login redirect missing for {username}: {status}")
    return client, headers.get("Location")


def seed(root):
    """Create only the isolated CI schema and test accounts."""
    config = {
        "database": {"host": "127.0.0.1", "port": 3307, "name": "ppl_study_ci",
                     "user": "ppl_ci", "password": "ppl-ci-only", "charset": "utf8mb4"},
        "base_url": BASE, "session_name": "ppl_ci_session",
        "csrf_secret": "ci-only-csrf-not-production", "setup_key": "ci-only-reset-not-production",
    }
    # All values above are static, disposable fixture configuration.
    (root / "config.php").write_text(
        "<?php return json_decode('" + json.dumps(config) + "', true, 512, JSON_THROW_ON_ERROR);\n",
        encoding="utf-8",
    )
    (root / "cache").mkdir()
    (root / "cache/installed.lock").write_text("ci\n")
    program = r"""
require 'app/bootstrap.php';
\PplStudy\Core\run_migrations();
$pdo = \PplStudy\Core\db();
$stmt = $pdo->prepare('INSERT INTO users (username,display_name,password_hash,role,must_change_password,active,created_at,updated_at) VALUES (?,?,?,?,?,1,NOW(),NOW())');
foreach ([['admin-ci','admin',0],['learner-ci','user',0],['pending-ci','admin',1]] as $user) {
    $stmt->execute([$user[0],$user[0],password_hash($argv[1],PASSWORD_DEFAULT),$user[1],$user[2]]);
}
"""
    subprocess.run(["php", "-r", program, PASSWORD], cwd=root, check=True)


def digest(content):
    """Return the journal-compatible SHA-256 hex digest."""
    return hashlib.sha256(content).hexdigest()


def exercise(root):
    """Verify actual route access and automatic recovery before dispatch."""
    guest = browser()
    for fields in (None, {"action": "force", "csrf_token": "invalid"}):
        status, headers, html = request(guest, "/admin/updates", fields)
        require(status == 302 and headers.get("Location") == BASE + "/login",
                "Guest must not reach updater")
        require("Vynutit aktualizaci" not in html, "Guest received admin content")

    learner, _ = login("learner-ci")
    for route in ("/admin/updates", "/?page=admin_updates"):
        status, headers, _ = request(learner, route, {"action": "force", "csrf_token": "invalid"})
        require(status == 302 and headers.get("Location") == BASE + "/account",
                "Learner must not invoke updater")

    pending, target = login("pending-ci")
    require(target == BASE + "/account/password", "Pending account password setup not enforced")
    status, headers, _ = request(pending, "/admin/updates")
    require(status == 302 and headers.get("Location") == BASE + "/account/password",
            "Pending administrator reached updater")

    admin, _ = login("admin-ci")
    status, headers, html = request(admin, "/admin/updates")
    require(status == 200 and "Aktualizace aplikace" in html, "Admin updater unavailable")
    require("no-store" in headers.get("Cache-Control", ""), "Admin page cache policy missing")
    require("Zkontrolovat aktualizace" in html, "Updater prerequisites not satisfied")
    token = csrf(html)
    status, _, html = request(admin, "/admin/updates", {"action": "force", "csrf_token": "invalid"})
    require(status == 403 and "Invalid CSRF token" in html, "Invalid CSRF accepted")
    status, _, html = request(admin, "/admin/updates", {"action": "invalid", "csrf_token": token})
    require(status == 200 and "Neznámá akce updateru." in html, "Invalid action not rejected")
    status, _, html = request(admin, "/admin/updates?action=force")
    require(status == 200 and "Aktualizace aplikace" in html, "GET must remain read-only")

    # Hold the real activation lock externally; PHP must return maintenance.
    with (root / "cache/ppl-update.lock").open("r+b") as handle:
        fcntl.flock(handle, fcntl.LOCK_EX | fcntl.LOCK_NB)
        status, headers, _ = request(admin, "/admin/updates")
        require(status == 503 and headers.get("Retry-After") == "10", "Activation gate failed")
        fcntl.flock(handle, fcntl.LOCK_UN)

    # Leave the exact recovery journal that a killed activation would leave.
    relative = "public/assets/app.css"
    live = root / relative
    original = live.read_bytes()
    changed = original + b"\n/* interrupted deployment fixture */\n"
    workspace = root / "cache/ppl-update"
    backup = workspace / "backup" / relative
    backup.parent.mkdir(parents=True)
    backup.write_bytes(original)
    live.write_bytes(changed)
    local_config = (root / "config.php").read_bytes()
    uploaded = root / "data/source-documents/fixture.pdf"
    uploaded.write_bytes(b"preserved local PDF fixture")
    journal = workspace / "activation.json"
    journal.write_text(json.dumps({"format": 1, "files": [{
        "path": relative, "old": digest(original), "new": digest(changed), "mode": 0o644,
    }]}), encoding="utf-8")

    status, _, _ = request(admin, "/admin/updates")
    require(status == 503, "Recovery request must not serve mixed runtime")
    require(live.read_bytes() == original and not journal.exists(), "Interrupted activation not restored")
    require((root / "config.php").read_bytes() == local_config, "Local config modified by recovery")
    require(uploaded.read_bytes() == b"preserved local PDF fixture", "Uploaded PDF changed")
    status, _, html = request(admin, "/admin/updates")
    require(status == 200 and "Aktualizace aplikace" in html, "Web did not recover")
    print("Updater HTTP authorization, CSRF, maintenance and recovery checks passed.")


def main():
    """Deploy the locally built CI artifact into a disposable web root."""
    archive = Path(sys.argv[1]).resolve()
    with tempfile.TemporaryDirectory(prefix="ppl-http-test-") as temporary:
        root = Path(temporary)
        with ZipFile(archive) as package:
            # This is the locally built and already verified CI fixture.
            package.extractall(root)
        seed(root)
        with (root / "server.log").open("w+b") as log:
            server = subprocess.Popen(["php", "-d", "display_errors=0", "-S", "127.0.0.1:8765", "index.php"],
                                      cwd=root, stdout=log, stderr=log)
            try:
                for _ in range(50):
                    try:
                        if request(browser(), "/login")[0] == 200:
                            break
                    except OSError:
                        time.sleep(0.1)
                else:
                    raise RuntimeError("PHP fixture did not start")
                exercise(root)
            except Exception:
                log.seek(0)
                print(log.read().decode(errors="replace"), file=sys.stderr)
                raise
            finally:
                server.terminate()
                server.wait(timeout=10)


if __name__ == "__main__":
    main()
