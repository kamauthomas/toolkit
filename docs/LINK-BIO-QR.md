# Toolkit Lnk.Bio QR

Encodes exactly `https://lnk.bio/The_Toolkit_Africa`. No query parameters,
Toolkit redirect, scan collector, or scan counter are added for this QR.

Files: `wp-content/themes/eduma-child/assets/images/link-bio-qr.png` and
`link-bio-qr.svg`. The SVG includes a white background for printing.
Toolkit Control displays a separate **Toolkit links QR code** panel with
preview, destination link, and PNG/SVG downloads.

Verification: independent PNG decoding at 888 px and 220 px; PHP/shell syntax.
Deploy with `scripts/deployment/releases/deploy-link-bio-qr-2026.09.30.2.sh`,
demo first, then production. Preserve the white border when placing the QR.

Release `2026.09.30.2` deployed demo first, then production. Fetched code and
QR assets matched the reviewed payload byte-for-byte. QR downloads, bare and
uncached application pages returned 200, and release headers matched. Local
dashboard render checks passed; existing application tracking checks passed.
Lnk.Bio's Cloudflare returned 403 to the automated destination request; verify
it with a phone/browser before printing a batch. No Toolkit tracking was added.
