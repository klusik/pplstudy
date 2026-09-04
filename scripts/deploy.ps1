<#
  Project: PPL Study
  File: scripts/deploy.ps1
  Module Type: Deployment Script

  Purpose:
    Creates a clean local deployment folder/ZIP or uploads the deployment set
    over FTP while preserving project-relative paths.

  Responsibilities:
    - Exclude local credentials, runtime cache, generated archives, and tooling cache
    - Preserve directory structure inside deployment ZIPs
    - Use a ZIP implementation compatible with ordinary hosting extractors
    - Work without invoking PHP or bootstrapping the web application

  Author: Rudolf Klusal
  License: MIT License for application code. See LICENSE.

  Notes:
    - Keep comments and docstrings intact when modifying this file.
#>

param(
    [ValidateSet('ftp', 'local')]
    [string]$Mode,
    [string]$HostName,
    [string]$UserName,
    [string]$Password,
    [string]$RemoteFolder,
    [string]$DeployFolder,
    [string]$MakeZipDeploy
)

$ErrorActionPreference = 'Stop'

if (-not $Mode) {
    $answer = Read-Host "Deployment mode: local deploy folder or FTP upload? [L/f]"
    $Mode = if ($answer -match '^[Ff]') { 'ftp' } else { 'local' }
}

if ($Mode -eq 'ftp') {
    if (-not $HostName) { $HostName = Read-Host "FTP host" }
    if (-not $UserName) { $UserName = Read-Host "FTP user" }
    if (-not $Password) { $Password = Read-Host "FTP password" }
    if (-not $RemoteFolder) { $RemoteFolder = Read-Host "Remote folder" }
}

if ($Mode -eq 'local' -and -not $DeployFolder) {
    $DeployFolder = Read-Host "Local deploy folder [deploy]"
    if (-not $DeployFolder) { $DeployFolder = 'deploy' }
}

$zipDeploy = $false
if ($Mode -eq 'local') {
    if ($PSBoundParameters.ContainsKey('MakeZipDeploy')) {
        $zipDeploy = ($MakeZipDeploy -match '^(1|true|yes|y)$')
    } else {
        $zipAnswer = Read-Host "Make a zip deploy? Y/n"
        $zipDeploy = -not ($zipAnswer -match '^[Nn]')
    }
}

$root = (Resolve-Path "$PSScriptRoot\..").Path
$metadataPath = Join-Path $root 'release-metadata.json'
$version = 'unknown'
if (Test-Path -LiteralPath $metadataPath) {
    try {
        $metadata = Get-Content -LiteralPath $metadataPath -Raw | ConvertFrom-Json
        if ($metadata.current_version) { $version = [string]$metadata.current_version }
    } catch {
        throw "release-metadata.json could not be read: $($_.Exception.Message)"
    }
}

$excludeTopLevelDirs = @('.git', 'cache', 'deploy')
$excludeDirNamesAnywhere = @('__pycache__', '.pytest_cache')
$excludeFiles = @('config.php', '.env', '.DS_Store', '*.log', '*.tmp', '*.pyc', '*.zip')

function Get-DeployRelativePath($Path) {
    $fullPath = [System.IO.Path]::GetFullPath($Path)
    $rootPath = [System.IO.Path]::GetFullPath($root).TrimEnd('\', '/')
    return $fullPath.Substring($rootPath.Length).TrimStart('\', '/')
}

function Should-Skip($Path) {
    if ($Mode -eq 'local' -and $script:DeployTarget) {
        $fullPath = [System.IO.Path]::GetFullPath($Path).TrimEnd('\', '/')
        $deployTargetPath = [System.IO.Path]::GetFullPath($script:DeployTarget).TrimEnd('\', '/')
        if ($fullPath -eq $deployTargetPath -or $fullPath.StartsWith($deployTargetPath + [System.IO.Path]::DirectorySeparatorChar)) {
            return $true
        }
    }

    $relative = Get-DeployRelativePath $Path
    $portableRelative = $relative.Replace('\', '/')

    foreach ($dir in $excludeTopLevelDirs) {
        $portableDir = $dir.Trim('/')
        if ($portableRelative -eq $portableDir -or $portableRelative.StartsWith($portableDir + '/')) {
            return $true
        }
    }

    foreach ($dirName in $excludeDirNamesAnywhere) {
        $escapedDirName = [regex]::Escape($dirName)
        if ($portableRelative -match "(^|/)$escapedDirName(/|$)") {
            return $true
        }
    }

    $leaf = Split-Path $Path -Leaf
    foreach ($pattern in $excludeFiles) {
        if ([System.Management.Automation.WildcardPattern]::new($pattern).IsMatch($leaf)) {
            return $true
        }
    }
    return $false
}

function Copy-DeployFile($LocalPath) {
    $relative = Get-DeployRelativePath $LocalPath
    $destination = Join-Path $script:DeployTarget $relative
    $destinationDir = Split-Path $destination -Parent
    if (-not (Test-Path -LiteralPath $destinationDir)) {
        New-Item -ItemType Directory -Path $destinationDir -Force | Out-Null
    }
    Copy-Item -LiteralPath $LocalPath -Destination $destination -Force
    Write-Host "Copied $relative"
}

function New-CompatibleZipArchive($SourceDirectory, $DestinationZip) {
    if (Test-Path -LiteralPath $DestinationZip) {
        Remove-Item -LiteralPath $DestinationZip -Force
    }

    Add-Type -AssemblyName System.IO.Compression
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    $sourcePath = [System.IO.Path]::GetFullPath($SourceDirectory).TrimEnd('\', '/')
    $archive = [System.IO.Compression.ZipFile]::Open($DestinationZip, [System.IO.Compression.ZipArchiveMode]::Create)
    try {
        Get-ChildItem -LiteralPath $sourcePath -Recurse -File | ForEach-Object {
            $entryName = $_.FullName.Substring($sourcePath.Length).TrimStart('\', '/').Replace('\', '/')
            [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
                $archive,
                $_.FullName,
                $entryName,
                [System.IO.Compression.CompressionLevel]::NoCompression
            ) | Out-Null
        }
    } finally {
        $archive.Dispose()
    }
}

function Ensure-RemoteDirectory($Uri) {
    $parts = ([Uri]$Uri).AbsolutePath.Trim('/').Split('/')
    $current = "ftp://$HostName"
    foreach ($part in $parts) {
        if (-not $part) { continue }
        $current = "$current/$part"
        try {
            $request = [System.Net.FtpWebRequest]::Create($current)
            $request.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $request.Credentials = New-Object System.Net.NetworkCredential($UserName, $Password)
            $response = $request.GetResponse()
            $response.Close()
        } catch {
            # Existing remote directories normally cause an FTP error here.
        }
    }
}

function Upload-File($LocalPath) {
    $relative = (Get-DeployRelativePath $LocalPath).Replace('\', '/')
    $remoteBase = ("ftp://{0}/{1}" -f $HostName, $RemoteFolder.Trim('/')).TrimEnd('/')
    $relativeDir = Split-Path $relative -Parent
    if ($relativeDir) {
        Ensure-RemoteDirectory "$remoteBase/$($relativeDir.Replace('\', '/'))"
    }
    $uri = "$remoteBase/$relative"
    $request = [System.Net.FtpWebRequest]::Create($uri)
    $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $request.Credentials = New-Object System.Net.NetworkCredential($UserName, $Password)
    $bytes = [System.IO.File]::ReadAllBytes($LocalPath)
    $request.ContentLength = $bytes.Length
    $stream = $request.GetRequestStream()
    try {
        $stream.Write($bytes, 0, $bytes.Length)
    } finally {
        $stream.Close()
    }
    $response = $request.GetResponse()
    $response.Close()
    Write-Host "Uploaded $relative"
}

Set-Location $root

if ($Mode -eq 'local') {
    $script:DeployTarget = if ([System.IO.Path]::IsPathRooted($DeployFolder)) {
        $DeployFolder
    } else {
        Join-Path $root $DeployFolder
    }

    $rootPath = [System.IO.Path]::GetFullPath($root).TrimEnd('\', '/')
    $deployTargetPath = [System.IO.Path]::GetFullPath($script:DeployTarget).TrimEnd('\', '/')
    if ($deployTargetPath -eq $rootPath) {
        throw "Local deploy folder cannot be the project root."
    }
}

$files = Get-ChildItem -LiteralPath $root -Recurse -File | Where-Object { -not (Should-Skip $_.FullName) }
if (-not $files) {
    throw 'No deployment files were selected.'
}

if ($Mode -eq 'local') {
    if (Test-Path -LiteralPath $script:DeployTarget) {
        Remove-Item -LiteralPath $script:DeployTarget -Recurse -Force
    }
    New-Item -ItemType Directory -Path $script:DeployTarget -Force | Out-Null

    if ($zipDeploy) {
        $deployStaging = Join-Path $env:TEMP ("ppl-study-deploy-{0}" -f ([guid]::NewGuid().ToString('N')))
        $previousDeployTarget = $script:DeployTarget
        try {
            $script:DeployTarget = $deployStaging
            New-Item -ItemType Directory -Path $script:DeployTarget -Force | Out-Null
            $files | ForEach-Object { Copy-DeployFile $_.FullName }

            $zipPath = Join-Path $previousDeployTarget ("PPL-Study-{0}-deploy.zip" -f $version)
            New-CompatibleZipArchive -SourceDirectory $deployStaging -DestinationZip $zipPath
            Write-Host "Local ZIP deploy created at $zipPath"
        } finally {
            $script:DeployTarget = $previousDeployTarget
            if (Test-Path -LiteralPath $deployStaging) {
                Remove-Item -LiteralPath $deployStaging -Recurse -Force
            }
        }
    } else {
        $files | ForEach-Object { Copy-DeployFile $_.FullName }
        Write-Host "Local deploy folder created at $script:DeployTarget"
    }
} else {
    $files | ForEach-Object { Upload-File $_.FullName }
}
