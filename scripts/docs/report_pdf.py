"""Internal report layout helpers, matching the existing institutional monthly format."""
import html
from io import BytesIO
from pathlib import Path
import cairo
import gi
from bs4 import NavigableString
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from PIL import Image
gi.require_version("Pango", "1.0")
gi.require_version("PangoCairo", "1.0")
from gi.repository import Pango, PangoCairo
REPO = Path(__file__).resolve().parents[2]
TEAL, INK, ORANGE, LIGHT, BORDER = "174a43", "26332f", "e46218", "dfe7e3", "c6ceca"

def markup(node):
    if isinstance(node, NavigableString):
        return html.escape(str(node))
    if node.name == "br":
        return "\n"
    inner = "".join(markup(child) for child in node.children)
    if node.name in {"strong", "b"}:
        return f"<b>{inner}</b>"
    if node.name in {"em", "i"}:
        return f"<i>{inner}</i>"
    return inner



def docx_inline(paragraph, node, bold=False, italic=False):
    if isinstance(node, NavigableString):
        run = paragraph.add_run(str(node))
        run.bold, run.italic = bold, italic
    elif node.name == "br":
        paragraph.add_run().add_break()
    else:
        for child in node.children:
            docx_inline(paragraph, child, bold or node.name in {"strong", "b"}, italic or node.name in {"em", "i"})


def shade(cell, fill):
    item = OxmlElement("w:shd")
    item.set(qn("w:fill"), fill)
    cell._tc.get_or_add_tcPr().append(item)



class PdfDocument:
    width, height, margin, bottom = 595.276, 841.89, 42.52, 788

    def __init__(self, path):
        self.surface = cairo.PDFSurface(str(path), self.width, self.height)
        self.surface.set_metadata(cairo.PDF_METADATA_TITLE, "Toolkit work report")
        self.context = cairo.Context(self.surface)
        self.y, self.page = 48, 1

    def color(self, value):
        self.context.set_source_rgb(*(int(value[i:i + 2], 16) / 255 for i in (0, 2, 4)))

    def layout(self, text, width, size, bold=False):
        layout = PangoCairo.create_layout(self.context)
        PangoCairo.context_set_resolution(layout.get_context(), 72)
        layout.set_font_description(Pango.FontDescription(f"Liberation Sans {'Bold ' if bold else ''}{size}"))
        layout.set_width(round(width * Pango.SCALE))
        layout.set_wrap(Pango.WrapMode.WORD_CHAR)
        layout.set_spacing(round(size * 0.18 * Pango.SCALE))
        layout.set_markup(text, -1)
        return layout, layout.get_size()[1] / Pango.SCALE

    def draw(self, layout, x, y, color=INK):
        self.color(color)
        self.context.move_to(x, y)
        PangoCairo.show_layout(self.context, layout)

    def finish_page(self):
        self.color(BORDER)
        self.context.set_line_width(0.5)
        self.context.move_to(self.margin, 803)
        self.context.line_to(self.width - self.margin, 803)
        self.context.stroke()
        label, _ = self.layout(f"Toolkit ICT | Internal | Page {self.page}", 510, 7)
        self.draw(label, self.margin, 812, "68736d")
        self.context.show_page()

    def new_page(self):
        self.finish_page()
        self.page += 1
        self.y = 48

    def logo(self, path=None):
        with Image.open(path or REPO / "docs/assets/toolkit-logo.png") as source:
            buffer = BytesIO()
            source.convert("RGBA").save(buffer, "PNG")
        buffer.seek(0)
        logo = cairo.ImageSurface.create_from_png(buffer)
        ratio = 132 / logo.get_width()
        self.context.save()
        self.context.translate(self.margin, self.y)
        self.context.scale(ratio, ratio)
        self.context.set_source_surface(logo, 0, 0)
        self.context.paint()
        self.context.restore()
        self.y += logo.get_height() * ratio + 10

    def paragraph(self, node):
        heading = node.name.startswith("h")
        size = {"h1": 23, "h2": 15, "h3": 11.5}.get(node.name, 9.5)
        before = 12 if heading else 0
        layout, height = self.layout(markup(node), self.width - self.margin * 2, size, heading)
        if self.y + before + height + (35 if heading else 0) > self.bottom:
            self.new_page()
        self.y += before
        self.draw(layout, self.margin, self.y, TEAL if heading else INK)
        self.y += height + 7
        if node.name == "h1":
            self.color(ORANGE)
            self.context.rectangle(self.margin, self.y, self.width - self.margin * 2, 3)
            self.context.fill()
            self.y += 12

    def table(self, node):
        widths = [(self.width - self.margin * 2) * fraction for fraction in proportions(node)]
        source_rows = node.find_all("tr")

        def prepare(row, header):
            layouts = [self.layout(markup(cell), width - 10, 7.7, header)
                       for cell, width in zip(row.find_all(["th", "td"]), widths)]
            return layouts, max(height for _, height in layouts) + 10

        def draw_row(layouts, height, header=False):
            x = self.margin
            for (layout, _), width in zip(layouts, widths):
                self.color(LIGHT if header else "ffffff")
                self.context.rectangle(x, self.y, width, height)
                self.context.fill_preserve()
                self.color(BORDER)
                self.context.set_line_width(0.5)
                self.context.stroke()
                self.draw(layout, x + 5, self.y + 5)
                x += width
            self.y += height

        header_layouts, header_height = prepare(source_rows[0], True)
        first_height = prepare(source_rows[1], False)[1] if len(source_rows) > 1 else 0
        if self.y + header_height + first_height > self.bottom:
            self.new_page()
        draw_row(header_layouts, header_height, True)
        for row in source_rows[1:]:
            layouts, height = prepare(row, False)
            if self.y + height > self.bottom:
                self.new_page()
                draw_row(header_layouts, header_height, True)
            draw_row(layouts, height)
        self.y += 11

    def finish(self):
        self.finish_page()
        self.surface.finish()
        return self.page
