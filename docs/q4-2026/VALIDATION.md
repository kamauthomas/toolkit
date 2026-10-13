# Verification — Q4 2026 OKRs

- Shared template reviewed: one Q4 2026 worksheet, four columns and six objective groups. Original template unchanged; SHA-256 `f48a7a64ac13da030fbb917badb0ae7956a5f7cf68b3aa9668c359fc40c15681`.
- Six objectives and 25 key results retained. All 25 rows match exactly between Excel and Word; every objective, key result, activity and timeline is present in both PDF text exports.
- Original spreadsheet logo retained byte-for-byte. Main spreadsheet prints over three landscape pages; the separate basis/notes worksheet prints on one page.
- Final Word PDF: three A4 landscape pages. All three pages visually reviewed; spreadsheet first-page layout reviewed. Text wraps without the earlier isolated-row overflow.
- Targets are proposed Q4 outcomes. Existing live corrections, local-only Reporting modules, Campus staging and pending providers/acceptance are distinguished in the basis notes. Poster work remains general and continuous.
- Initial sandboxed LibreOffice conversion failed with `unable to create file /run/user/1000/dconf/user: Read-only file system`; a generic-plugin attempt returned `X11 error: Can't open display: :0`. Conversion succeeded using approved execution outside the sandbox and fresh disposable profiles; the user's active LibreOffice profile was not used.
- The first PDF-content assertion used layout text, which interleaves columns, and failed to find the first complete objective. Rechecking with raw PDF text passed for all cells in both PDF exports; the document content did not need changing.
- The first Git whitespace check flagged three intentional Markdown line breaks as trailing whitespace. They were replaced with paragraph breaks; the final check passed.
- No application tests or live-system probes were required for this document-only change. No deployment, operational write or report-system submission took place.
