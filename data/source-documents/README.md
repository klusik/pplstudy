# Protected source documents

This directory is reserved for source PDFs associated by an administrator.

PDFs are deliberately excluded from version control and release packages. Keep original source files outside the deployed project. When a source document is needed in an installation, associate a locally supplied PDF with its entry from `data/source-manifest.json` through **Admin > Zdroje**.

Direct HTTP access is denied by `.htaccess`. PPL Study serves an associated local document only through an authenticated route. The manifest retains stable source keys, official URLs, expected filenames, version labels, and known SHA-256 metadata without requiring a PDF to be bundled.
