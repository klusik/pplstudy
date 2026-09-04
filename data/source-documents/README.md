# Protected source documents

Verified source PDFs are stored in this directory.

The directory is denied to direct HTTP access by `.htaccess`. PPL Study serves
a local source document only through an authenticated route.

The project owner directly supplied the official PDFs for all nine subjects.
The files `01-pravni-predpisy-official.pdf` through
`09-navigace-official.pdf` are bundled and referenced by their source-exact
question sets. `data/source-manifest.json` stores the expected local filename
and SHA-256 digest for each supplied source.

Additional source PDFs can be associated with entries from
`data/source-manifest.json` through **Admin > Zdroje**. An administrator upload
for the same source key replaces the local file used by the authenticated source
route.
