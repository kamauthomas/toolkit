#!/usr/bin/env python3
"""Generate the two-page QR change report (requires reportlab)."""
from pathlib import Path
from html import escape
from reportlab.pdfgen import canvas
from reportlab.lib import colors
from reportlab.lib.styles import ParagraphStyle
from reportlab.lib.enums import TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.platypus import Paragraph
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / 'docs/Toolkit-QR-Code-Changes-2026-09-30.pdf'
FONT = Path('/usr/share/fonts/truetype/dejavu')
pdfmetrics.registerFont(TTFont('Toolkit', str(FONT / 'DejaVuSans.ttf')))
pdfmetrics.registerFont(TTFont('ToolkitBold', str(FONT / 'DejaVuSans-Bold.ttf')))
pdfmetrics.registerFontFamily('Toolkit', normal='Toolkit', bold='ToolkitBold')
NAVY = colors.HexColor('#183447')
TEAL = colors.HexColor('#087F8C')
MUTED = colors.HexColor('#526474')
LIGHT = colors.HexColor('#EDF4F6')
W, H = A4
M = 43
STYLE = ParagraphStyle('body', fontName='Toolkit', fontSize=9.2, leading=14, textColor=NAVY, spaceAfter=8)
SMALL = ParagraphStyle('small', parent=STYLE, fontSize=8, leading=12)
TITLE = ParagraphStyle('title', parent=STYLE, fontName='ToolkitBold', fontSize=23, leading=29)
SUB = ParagraphStyle('sub', parent=STYLE, fontName='ToolkitBold', fontSize=12, leading=17, textColor=TEAL)
APP = 'https://toolkitafrica.ac.ke/apply/?utm_source=application_qr&utm_medium=qr&utm_campaign=admissions'
BIO = 'https://lnk.bio/The_Toolkit_Africa'
BASE = 'https://toolkitafrica.ac.ke/wp-content/themes/eduma-child/assets/images/'
OUT.parent.mkdir(parents=True, exist_ok=True)
c = canvas.Canvas(str(OUT), pagesize=A4)
c.setTitle('Toolkit QR Code Changes — 30 September 2026')
c.setAuthor('The Toolkit for Skills and Innovation')
c.setSubject('Application QR, untracked Lnk.Bio QR, dashboard updates and legacy Canva transition')

def p(text, y, style=STYLE, x=M, width=W-2*M):
    para = Paragraph(text, style)
    _, height = para.wrap(width, H)
    para.drawOn(c, x, y-height)
    return y-height-9

def heading(text, y):
    return p(text, y-5, SUB)

def frame(page):
    c.setFillColor(NAVY); c.rect(0, H-11, W, 11, fill=1, stroke=0)
    c.setFont('ToolkitBold', 9); c.drawString(M, H-40, 'THE TOOLKIT FOR SKILLS AND INNOVATION')
    c.setFillColor(MUTED); c.setFont('Toolkit', 8); c.drawRightString(W-M, H-40, '30 SEPTEMBER 2026')
    c.setStrokeColor(colors.HexColor('#D4DFE3')); c.line(M, 38, W-M, 38)
    c.setFont('Toolkit', 7.5); c.drawString(M, 24, 'QR change report  |  Website & communications')
    c.drawRightString(W-M, 24, f'{page} / 2')

frame(1)
y = p('QR code changes', H-69, TITLE)
y = p('Application access, social links and Toolkit Control', y, SUB)
y = p('Two new QR codes are available on the live Toolkit website and in the custom Toolkit Control dashboard. The application QR opens the existing Toolkit application form and records aggregate arrivals. The socials QR opens Lnk.Bio directly, with no Toolkit tracking added.', y)
y = heading('1. Application form QR', y)
y = p('The QR opens <b>toolkitafrica.ac.ke/apply/</b>, the live custom Toolkit form. It does not take applicants to the Mzizi interface. The existing form, admissions workflow and server-side Mzizi integration were retained.', y)
y = p('Toolkit Control now provides a QR preview, PNG/SVG downloads, and <b>30-day and all-time application QR scan counts</b>. Site analytics also shows the count for the selected 30-, 90-, 365-day or all-time period.', y)
y = p('The count measures tagged form openings reported by the browser, once per browser session when storage is available. Do Not Track is respected. Camera scans that never open the page, blocked analytics and disabled JavaScript are excluded. Shared QR links can also count as arrivals; the figures do not represent unique people or completed applications.', y, SMALL)
y = heading('2. Social links QR', y)
y = p('The second QR encodes exactly <b>https://lnk.bio/The_Toolkit_Africa</b>. It uses no added tracking parameters, Toolkit redirect, scan collector or scan counter. A separate <b>Toolkit links QR code</b> panel provides the preview, destination link and PNG/SVG downloads.', y)
y = heading('3. Shift from the legacy Canva socials QR', y)
y = p('The original socials entry point was hosted through Canva, as described by the project owner. For new or revised materials, the new Lnk.Bio QR becomes the socials entry point; the application QR serves admissions separately.', y)
y = p('<b>Previous:</b> Canva-hosted Toolkit socials destination.<br/><b>Now:</b> a direct Lnk.Bio QR for social links, plus a separate Toolkit application QR for admissions.', y)
y = p('Replace the old Canva socials QR artwork in future posters, brochures and digital materials with the new Lnk.Bio QR. Existing printed codes retain their encoded destination. This release did not edit the old Canva destination, retarget previously printed QR codes, or migrate historical Canva analytics.', y, SMALL)
assert y > 50, y
c.showPage()
frame(2)
y = p('QR assets & release record', H-69, TITLE)
y = p('Use the application code for admissions and the links code for socials.', y)
card_y = y-12
card_width = (W-2*M-20)/2
cards = [('Application form', 'application-qr.png', 'application-qr.svg', APP, 'Toolkit arrival tracking enabled'), ('Toolkit social links', 'link-bio-qr.png', 'link-bio-qr.svg', BIO, 'No Toolkit scan tracking added')]
for i, (title, png, svg, url, detail) in enumerate(cards):
    x=M+i*(card_width+20)
    c.setFillColor(LIGHT); c.roundRect(x, card_y-266, card_width, 266, 8, fill=1, stroke=0)
    p(title, card_y-13, SUB, x+13, card_width-26)
    c.drawImage(str(ROOT/'wp-content/themes/eduma-child/assets/images'/png), x+(card_width-144)/2, card_y-188, width=144, height=144)
    yy=p(detail, card_y-197, SMALL, x+13, card_width-26)
    p(f'<a href="{BASE+png}" color="#087F8C">Download PNG</a> &nbsp; | &nbsp; <a href="{BASE+svg}" color="#087F8C">Download print SVG</a><br/><a href="{escape(url, quote=True)}" color="#087F8C">Open destination</a>', yy, SMALL, x+13, card_width-26)
y=card_y-286
y=heading('Deployment and checks',y)
y=p('<b>30 September 2026:</b> release <b>2026.09.30.1</b> added the application QR and scan metrics; release <b>2026.09.30.2</b> added the untracked Lnk.Bio QR. Both followed demo deployment and verification before production.',y)
y=p('Code and QR assets were verified byte-for-byte after deployment. The application page and QR downloads returned HTTP 200. Both QR images decoded to their expected destinations at full size and at the 220 px dashboard preview size. Existing application tracking checks passed after the socials QR was added.',y)
y=p('The demo metrics collector accepted one test application-QR event (HTTP 204) and rejected an originless request (HTTP 403). No production test applications or synthetic production scan events were submitted. Dashboard rendering was checked locally; staff acceptance of the authenticated live dashboard remains pending.',y,SMALL)
y=heading('Operational handover',y)
y=p('Download both assets from <b>WordPress → Toolkit Control</b>. Use PNG for digital layouts and SVG for scalable print layouts. Keep the white border intact and phone-scan a printed sample before printing a batch.',y)
y=p('Lnk.Bio blocked the automated destination request with Cloudflare HTTP 403. Its QR encoding is verified, but the destination still needs a normal phone/browser check. No claim is made about analytics operated by Lnk.Bio itself.',y,SMALL)
y=p('<b>Source records:</b> WORKLOG.md; docs/APPLICATION-QR.md; docs/LINK-BIO-QR.md; implementation commits 2b065b3 and c71034d. The legacy Canva hosting context was supplied by the project owner.',y,SMALL)
assert y > 50,y
c.save()
print(OUT)
