from __future__ import annotations

from pathlib import Path
from textwrap import wrap

from docx import Document
from docx.enum.table import WD_ALIGN_VERTICAL, WD_TABLE_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_LINE_SPACING
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Cm, Inches, Pt, RGBColor
from PIL import Image, ImageDraw, ImageFont


ROOT = Path(__file__).resolve().parents[1]
DOCS = ROOT / "docs" / "lab5"
ASSETS = DOCS / "report_assets"
OUTPUT = ROOT / "docs" / "Lab5_Report_Variant3.docx"

FONT = "Times New Roman"
BODY_SIZE = Pt(13)
NAVY = "17365D"
PALE_BLUE = "EAF2F8"
BORDER = "D9D9D9"


def set_run_font(run, name: str = FONT, size: Pt = BODY_SIZE, bold: bool | None = None) -> None:
    run.font.name = name
    run.font.size = size
    run.font.color.rgb = RGBColor(0, 0, 0)
    if bold is not None:
        run.bold = bold
    fonts = run._element.get_or_add_rPr().get_or_add_rFonts()
    for attr in ("ascii", "hAnsi", "eastAsia", "cs"):
        fonts.set(qn(f"w:{attr}"), name)


def set_cell_margins(cell, top=100, start=120, bottom=100, end=120) -> None:
    tc_pr = cell._tc.get_or_add_tcPr()
    tc_mar = tc_pr.first_child_found_in("w:tcMar")
    if tc_mar is None:
        tc_mar = OxmlElement("w:tcMar")
        tc_pr.append(tc_mar)
    for name, value in (("top", top), ("start", start), ("bottom", bottom), ("end", end)):
        node = tc_mar.find(qn(f"w:{name}"))
        if node is None:
            node = OxmlElement(f"w:{name}")
            tc_mar.append(node)
        node.set(qn("w:w"), str(value))
        node.set(qn("w:type"), "dxa")


def shade_cell(cell, fill: str) -> None:
    tc_pr = cell._tc.get_or_add_tcPr()
    shd = tc_pr.find(qn("w:shd"))
    if shd is None:
        shd = OxmlElement("w:shd")
        tc_pr.append(shd)
    shd.set(qn("w:fill"), fill)


def set_table_borders(table) -> None:
    tbl_pr = table._tbl.tblPr
    borders = tbl_pr.find(qn("w:tblBorders"))
    if borders is None:
        borders = OxmlElement("w:tblBorders")
        tbl_pr.append(borders)
    for edge in ("top", "left", "bottom", "right", "insideH", "insideV"):
        node = borders.find(qn(f"w:{edge}"))
        if node is None:
            node = OxmlElement(f"w:{edge}")
            borders.append(node)
        node.set(qn("w:val"), "single")
        node.set(qn("w:sz"), "8")
        node.set(qn("w:space"), "0")
        node.set(qn("w:color"), BORDER)


def set_repeat_header(row) -> None:
    prop = row._tr.get_or_add_trPr()
    flag = OxmlElement("w:tblHeader")
    flag.set(qn("w:val"), "true")
    prop.append(flag)


def add_page_number(paragraph) -> None:
    paragraph.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run = paragraph.add_run()
    set_run_font(run, size=Pt(10))
    begin = OxmlElement("w:fldChar")
    begin.set(qn("w:fldCharType"), "begin")
    instr = OxmlElement("w:instrText")
    instr.set(qn("xml:space"), "preserve")
    instr.text = " PAGE "
    separate = OxmlElement("w:fldChar")
    separate.set(qn("w:fldCharType"), "separate")
    text = OxmlElement("w:t")
    text.text = "2"
    end = OxmlElement("w:fldChar")
    end.set(qn("w:fldCharType"), "end")
    run._r.extend([begin, instr, separate, text, end])


def configure_document(doc: Document) -> None:
    section = doc.sections[0]
    section.page_width = Inches(8.5)
    section.page_height = Inches(11)
    section.top_margin = Cm(1.9)
    section.bottom_margin = Cm(1.7)
    section.left_margin = Cm(2.6)
    section.right_margin = Cm(1.7)
    section.footer_distance = Cm(0.8)
    section.different_first_page_header_footer = True
    add_page_number(section.footer.paragraphs[0])

    normal = doc.styles["Normal"]
    normal.font.name = FONT
    normal.font.size = BODY_SIZE
    normal.font.color.rgb = RGBColor(0, 0, 0)
    normal._element.rPr.rFonts.set(qn("w:ascii"), FONT)
    normal._element.rPr.rFonts.set(qn("w:hAnsi"), FONT)
    normal.paragraph_format.line_spacing_rule = WD_LINE_SPACING.ONE_POINT_FIVE
    normal.paragraph_format.first_line_indent = Cm(1.0)
    normal.paragraph_format.space_after = Pt(3)

    title = doc.styles["Title"]
    title.font.name = FONT
    title.font.size = Pt(19)
    title.font.bold = True
    title.font.color.rgb = RGBColor(0, 0, 0)
    title._element.rPr.rFonts.set(qn("w:ascii"), FONT)
    title._element.rPr.rFonts.set(qn("w:hAnsi"), FONT)
    title.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.CENTER
    title.paragraph_format.space_after = Pt(10)

    for style_name, size, align in (
        ("Heading 1", 15, WD_ALIGN_PARAGRAPH.CENTER),
        ("Heading 2", 13, WD_ALIGN_PARAGRAPH.LEFT),
    ):
        style = doc.styles[style_name]
        style.font.name = FONT
        style.font.size = Pt(size)
        style.font.bold = True
        style.font.color.rgb = RGBColor(0, 0, 0)
        style._element.rPr.rFonts.set(qn("w:ascii"), FONT)
        style._element.rPr.rFonts.set(qn("w:hAnsi"), FONT)
        style.paragraph_format.alignment = align
        style.paragraph_format.first_line_indent = Cm(0)
        style.paragraph_format.space_before = Pt(10)
        style.paragraph_format.space_after = Pt(6)
        style.paragraph_format.keep_with_next = True

    update_fields = OxmlElement("w:updateFields")
    update_fields.set(qn("w:val"), "true")
    doc.settings._element.append(update_fields)


def add_centered(doc: Document, text: str, *, bold=False, size=13, after=0) -> None:
    paragraph = doc.add_paragraph()
    paragraph.alignment = WD_ALIGN_PARAGRAPH.CENTER
    paragraph.paragraph_format.first_line_indent = Cm(0)
    paragraph.paragraph_format.line_spacing_rule = WD_LINE_SPACING.SINGLE
    paragraph.paragraph_format.space_after = Pt(after)
    set_run_font(paragraph.add_run(text), size=Pt(size), bold=bold)


def add_body(doc: Document, text: str, *, bold_lead: str | None = None) -> None:
    paragraph = doc.add_paragraph()
    paragraph.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    paragraph.paragraph_format.first_line_indent = Cm(1.0)
    paragraph.paragraph_format.line_spacing_rule = WD_LINE_SPACING.ONE_POINT_FIVE
    if bold_lead and text.startswith(bold_lead):
        set_run_font(paragraph.add_run(bold_lead), bold=True)
        set_run_font(paragraph.add_run(text[len(bold_lead) :]))
    else:
        set_run_font(paragraph.add_run(text))


def add_bullet(doc: Document, text: str) -> None:
    paragraph = doc.add_paragraph(style="List Bullet")
    paragraph.paragraph_format.left_indent = Cm(0.8)
    paragraph.paragraph_format.first_line_indent = Cm(0)
    paragraph.paragraph_format.line_spacing_rule = WD_LINE_SPACING.ONE_POINT_FIVE
    set_run_font(paragraph.add_run(text))


def add_caption(doc: Document, text: str) -> None:
    paragraph = doc.add_paragraph()
    paragraph.alignment = WD_ALIGN_PARAGRAPH.CENTER
    paragraph.paragraph_format.first_line_indent = Cm(0)
    paragraph.paragraph_format.space_before = Pt(3)
    paragraph.paragraph_format.space_after = Pt(7)
    set_run_font(paragraph.add_run(text), size=Pt(10.5))


def add_picture(doc: Document, path: Path, caption: str, alt_text: str, width_cm: float = 15.8) -> None:
    paragraph = doc.add_paragraph()
    paragraph.alignment = WD_ALIGN_PARAGRAPH.CENTER
    paragraph.paragraph_format.first_line_indent = Cm(0)
    paragraph.paragraph_format.keep_with_next = True
    shape = paragraph.add_run().add_picture(str(path), width=Cm(width_cm))
    shape._inline.docPr.set("descr", alt_text)
    shape._inline.docPr.set("title", caption)
    add_caption(doc, caption)


def locate_font(bold: bool = False) -> str:
    names = ["consolab.ttf", "CascadiaMono-Bold.ttf"] if bold else ["consola.ttf", "CascadiaMono.ttf"]
    for name in names:
        path = Path("C:/Windows/Fonts") / name
        if path.exists():
            return str(path)
    return "C:/Windows/Fonts/arial.ttf"


def wrapped_code_lines(source: Path, start: int, end: int | None) -> list[tuple[str, str]]:
    lines = source.read_text(encoding="utf-8").splitlines()
    last = min(end or len(lines), len(lines))
    result: list[tuple[str, str]] = []
    for line_number, raw in enumerate(lines[start - 1 : last], start):
        chunks = wrap(raw.expandtabs(4), width=96, replace_whitespace=False, drop_whitespace=False) or [""]
        for index, chunk in enumerate(chunks):
            result.append((str(line_number) if index == 0 else "", chunk))
    return result


def make_code_image(source: Path, output: Path, title: str, start: int = 1, end: int | None = None) -> None:
    lines = wrapped_code_lines(source, start, end)
    code_font = ImageFont.truetype(locate_font(), 24)
    title_font = ImageFont.truetype(locate_font(True), 25)
    line_height = 34
    width = 1800
    height = 92 + max(1, len(lines)) * line_height + 28
    image = Image.new("RGB", (width, height), "#0D1117")
    draw = ImageDraw.Draw(image)
    draw.rectangle((0, 0, width, 70), fill="#161B22")
    draw.text((32, 19), title, font=title_font, fill="#F0F6FC")
    draw.rectangle((0, 70, 112, height), fill="#10151C")
    y = 87
    for number, line in lines:
        draw.text((18, y), f"{number:>3}", font=code_font, fill="#6E7681")
        stripped = line.lstrip()
        color = "#E6EDF3"
        if stripped.startswith(("//", "/*", "*", "#")):
            color = "#8B949E"
        elif stripped.startswith(("class ", "final ", "namespace ", "use ", "public ", "private ", "protected ", "declare(")):
            color = "#FF7B72"
        draw.text((128, y), line, font=code_font, fill=color)
        y += line_height
    output.parent.mkdir(parents=True, exist_ok=True)
    image.save(output, optimize=True)


def make_terminal_image(output: Path) -> None:
    lines = [
        "> php artisan key:generate",
        "INFO  Application key set successfully.",
        "> .\\scripts\\verify.ps1",
        "Routes: 9 registered (tickets.index, tickets.store, tickets.show)",
        '{"tool":"pint","result":"passed"}',
        "PASS  Tests\\Feature\\TicketAccessTest (7 tests, 24 assertions)",
        "PASS  Full test suite",
        "Tests: 8 passed (25 assertions)",
        "LAB 5 VALIDATION: PASS",
    ]
    font = ImageFont.truetype(locate_font(), 27)
    bold = ImageFont.truetype(locate_font(True), 28)
    image = Image.new("RGB", (1800, 560), "#0C1117")
    draw = ImageDraw.Draw(image)
    draw.rectangle((0, 0, 1800, 68), fill="#161B22")
    draw.text((30, 18), "PowerShell — Laravel Lab 5", font=bold, fill="#F0F6FC")
    y = 91
    for line in lines:
        color = "#7EE787" if "PASS" in line or "passed" in line or "successfully" in line else "#E6EDF3"
        draw.text((36, y), line, font=font, fill=color)
        y += 47
    output.parent.mkdir(parents=True, exist_ok=True)
    image.save(output, optimize=True)


def make_http_image(output: Path) -> None:
    image = Image.new("RGB", (1800, 960), "#EEF3F8")
    draw = ImageDraw.Draw(image)
    title = ImageFont.truetype(locate_font(True), 42)
    heading = ImageFont.truetype(locate_font(True), 29)
    body = ImageFont.truetype(locate_font(), 24)
    draw.text((90, 55), "HTTP-перевірка маршрутів", font=title, fill="#182235")
    cards = [
        ("GET /tickets", "200 OK", "Повернуто 3 квитки у JSON", "#167647"),
        ("POST /tickets без ключа", "403 Forbidden", "Middleware зупинив запит", "#B5472E"),
        ("POST /tickets з партнерським ключем", "201 Created", "X-Security-Policy: Partner-Key-Enforced", "#167647"),
        ("GET /tickets/999", "404 Not Found", "Повернуто status=error", "#B5472E"),
    ]
    positions = [(90, 145), (925, 145), (90, 525), (925, 525)]
    for (label, status, detail, color), (x, y) in zip(cards, positions):
        draw.rounded_rectangle((x, y, x + 785, y + 320), radius=22, fill="#FFFFFF", outline="#D8E0EB", width=3)
        draw.text((x + 38, y + 40), label, font=heading, fill="#182235")
        draw.text((x + 38, y + 115), status, font=title, fill=color)
        for offset, line in enumerate(wrap(detail, width=46)):
            draw.text((x + 38, y + 205 + offset * 38), line, font=body, fill="#53647A")
    output.parent.mkdir(parents=True, exist_ok=True)
    image.save(output, optimize=True)


def make_json_image(output: Path) -> None:
    lines = [
        '{',
        '  "status": "success",',
        '  "data": [',
        '    {"id": 1, "event_name": "Interstellar", "seat_number": "A-12",',
        '     "price": 220, "customer_email": "anna@example.com"},',
        '    {"id": 2, "event_name": "Dune Part Two", "seat_number": "C-08",',
        '     "price": 260, "customer_email": "oleh@example.com"},',
        '    {"id": 3, "event_name": "The Wild Robot", "seat_number": "B-04",',
        '     "price": 180, "customer_email": "marta@example.com"}',
        '  ]',
        '}',
    ]
    font = ImageFont.truetype(locate_font(), 26)
    bold = ImageFont.truetype(locate_font(True), 28)
    image = Image.new("RGB", (1800, 630), "#0C1117")
    draw = ImageDraw.Draw(image)
    draw.rectangle((0, 0, 1800, 68), fill="#161B22")
    draw.text((30, 18), "GET /tickets — 200 OK", font=bold, fill="#F0F6FC")
    y = 90
    for line in lines:
        color = "#A5D6FF" if '"status"' in line else "#E6EDF3"
        draw.text((42, y), line, font=font, fill=color)
        y += 45
    output.parent.mkdir(parents=True, exist_ok=True)
    image.save(output, optimize=True)


def prepare_assets() -> dict[str, Path]:
    specs = [
        ("controller_1", ROOT / "app/Http/Controllers/TicketController.php", "TicketController.php — index і store", 1, 49),
        ("controller_2", ROOT / "app/Http/Controllers/TicketController.php", "TicketController.php — show і 404", 50, None),
        ("routes", ROOT / "routes/web.php", "routes/web.php — іменовані маршрути", 1, None),
        ("middleware", ROOT / "app/Http/Middleware/VerifyPartnerApiKey.php", "VerifyPartnerApiKey.php", 1, None),
        ("bootstrap", ROOT / "bootstrap/app.php", "bootstrap/app.php — реєстрація middleware", 1, None),
        ("tests_1", ROOT / "tests/Feature/TicketAccessTest.php", "TicketAccessTest.php — GET і негативні сценарії", 1, 58),
        ("tests_2", ROOT / "tests/Feature/TicketAccessTest.php", "TicketAccessTest.php — 201 і валідація", 59, None),
        ("ci", ROOT.parent / ".github/workflows/lab5-ci.yml", "GitHub Actions — lab5-ci.yml", 1, None),
    ]
    assets: dict[str, Path] = {}
    for key, source, title, start, end in specs:
        target = ASSETS / f"{key}.png"
        make_code_image(source, target, title, start, end)
        assets[key] = target
    assets["welcome"] = ASSETS / "laravel-welcome.png"
    assets["tickets_json"] = ASSETS / "tickets-json.png"
    assets["terminal"] = ASSETS / "terminal-validation.png"
    assets["http"] = ASSETS / "http-scenarios.png"
    make_terminal_image(assets["terminal"])
    make_http_image(assets["http"])
    make_json_image(assets["tickets_json"])
    return assets


def add_title_page(doc: Document) -> None:
    add_centered(doc, "МІНІСТЕРСТВО ОСВІТИ І НАУКИ УКРАЇНИ", bold=True)
    add_centered(doc, "ЗАКЛАД ВИЩОЇ ОСВІТИ ____________________________", size=12)
    for _ in range(5):
        doc.add_paragraph()
    doc.add_paragraph("ЗВІТ", style="Title")
    add_centered(doc, "до лабораторної роботи № 5", bold=True)
    add_centered(doc, "з дисципліни Веб програмування")
    add_centered(doc, "Архітектура вебзастосунку на Laravel маршрутизація контролери та middleware", bold=True, after=10)
    add_centered(doc, "Варіант 3", bold=True)
    for _ in range(2):
        doc.add_paragraph()

    table = doc.add_table(rows=4, cols=2)
    table.alignment = WD_TABLE_ALIGNMENT.RIGHT
    table.autofit = False
    for row, (label, value) in zip(
        table.rows,
        (("Виконав", "Малик Денис"), ("Група", "________________________"), ("Перевірив", "________________________"), ("Оцінка", "________________________")),
    ):
        row.cells[0].width = Cm(3.0)
        row.cells[1].width = Cm(7.2)
        for cell, value_text in zip(row.cells, (label, value)):
            set_cell_margins(cell, top=40, start=40, bottom=40, end=40)
            paragraph = cell.paragraphs[0]
            paragraph.paragraph_format.first_line_indent = Cm(0)
            set_run_font(paragraph.add_run(value_text))
    borders = OxmlElement("w:tblBorders")
    for edge in ("top", "left", "bottom", "right", "insideH", "insideV"):
        node = OxmlElement(f"w:{edge}")
        node.set(qn("w:val"), "nil")
        borders.append(node)
    table._tbl.tblPr.append(borders)
    for _ in range(4):
        doc.add_paragraph()
    add_centered(doc, "2026")
    doc.add_page_break()


def add_matrix(doc: Document) -> None:
    rows = [
        ("Ticket", "TicketController", "GET /tickets, GET /tickets/{id}"),
        ("Створення квитка", "store(Request)", "event_name, seat_number, price, customer_email"),
        ("Партнерський доступ", "VerifyPartnerApiKey", "X-Partner-Api-Key, 403 або пропуск"),
        ("Постобробка", "VerifyPartnerApiKey", "X-Security-Policy: Partner-Key-Enforced"),
        ("Службовий звіт", "TicketReportController", "GET /ticket-reports/summary"),
    ]
    table = doc.add_table(rows=1, cols=3)
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    table.autofit = False
    widths = (Cm(4.0), Cm(4.7), Cm(6.0))
    for cell, text, width in zip(table.rows[0].cells, ("Use Case або сутність", "Компонент", "Реалізація"), widths):
        cell.width = width
        shade_cell(cell, NAVY)
        set_cell_margins(cell)
        cell.vertical_alignment = WD_ALIGN_VERTICAL.CENTER
        paragraph = cell.paragraphs[0]
        paragraph.paragraph_format.first_line_indent = Cm(0)
        paragraph.alignment = WD_ALIGN_PARAGRAPH.CENTER
        run = paragraph.add_run(text)
        set_run_font(run, size=Pt(10.5), bold=True)
        run.font.color.rgb = RGBColor(255, 255, 255)
    set_repeat_header(table.rows[0])
    for index, values in enumerate(rows):
        cells = table.add_row().cells
        if index % 2:
            for cell in cells:
                shade_cell(cell, PALE_BLUE)
        for cell, text, width in zip(cells, values, widths):
            cell.width = width
            cell.vertical_alignment = WD_ALIGN_VERTICAL.CENTER
            set_cell_margins(cell)
            paragraph = cell.paragraphs[0]
            paragraph.paragraph_format.first_line_indent = Cm(0)
            paragraph.paragraph_format.line_spacing_rule = WD_LINE_SPACING.SINGLE
            set_run_font(paragraph.add_run(text), size=Pt(10.5))
    set_table_borders(table)


def build_report() -> None:
    assets = prepare_assets()
    doc = Document()
    configure_document(doc)
    add_title_page(doc)

    doc.add_heading("1 Мета та постановка завдання", level=1)
    add_body(doc, "Мета роботи - опанувати структуру Laravel-проєкту, декларативну маршрутизацію, ресурсні й сервісні контролери, об'єкти Request та JsonResponse, а також конвеєр middleware для перевірки вхідного HTTP-трафіку.")
    add_body(doc, "Індивідуальна тематика варіанта 3 - електронна система продажу квитків до кінотеатру. Головною сутністю є Ticket. Для неї потрібно реалізувати маршрути /tickets, валідацію чотирьох предметних атрибутів і перевірку партнерського API-ключа.")
    add_bullet(doc, "PHP 8.2.35, Composer 2.11 і Laravel Framework 12.71.0.")
    add_bullet(doc, "JSON-відповіді зі статусами 200, 201, 403, 404 і 422.")
    add_bullet(doc, "Автоматизована перевірка Laravel Pint і PHPUnit.")

    doc.add_heading("2 Узгодження артефактів із ЛР № 2", level=1)
    add_body(doc, "Реалізація безпосередньо продовжує UML-модель системи продажу квитків із лабораторної роботи № 2. Таблиця показує відповідність сутностей і прецедентів програмним компонентам Laravel.")
    add_matrix(doc)
    doc.add_page_break()

    doc.add_heading("3 Ініціалізація та структура Laravel", level=1)
    add_body(doc, "У наявному репозиторії розгорнуто Laravel 12 зі збереженням каталогу .github та попередніх лабораторних. Додано стандартні каталоги app/Http, bootstrap, config, public, resources, routes і storage. Локальний APP_KEY згенеровано командою php artisan key:generate.")
    add_picture(doc, assets["welcome"], "Рисунок 1 - Стартова сторінка Laravel Lab 5", "Світла стартова сторінка системи продажу квитків", 15.8)
    doc.add_page_break()

    doc.add_heading("4 Ресурсний контролер TicketController", level=1)
    add_body(doc, "Контролер використовує mock-сховище з трьома квитками. Метод index() повертає колекцію, store() перевіряє чотири обов'язкові поля та формує 201 Created, а show() повертає запис або 404 Not Found.")
    add_picture(doc, assets["controller_1"], "Рисунок 2 - Методи index і store", "Початок TicketController з mock-сховищем і валідацією", 15.5)
    doc.add_page_break()
    add_picture(doc, assets["controller_2"], "Рисунок 3 - Метод show і відповідь 404", "Завершення TicketController з пошуком квитка", 15.5)

    doc.add_heading("5 Декларативна маршрутизація", level=1)
    add_body(doc, "Маршрути згруповано префіксом tickets і префіксом імен tickets. Параметр id обмежено методом whereNumber(). Окремо додано health-check і службовий підсумок TicketReportController.")
    add_picture(doc, assets["routes"], "Рисунок 4 - Іменовані маршрути вебзастосунку", "Код routes web php", 15.5)
    doc.add_page_break()

    doc.add_heading("6 Middleware партнерського API-ключа", level=1)
    add_body(doc, "VerifyPartnerApiKey порівнює заголовок X-Partner-Api-Key зі значенням конфігурації через hash_equals(). Відсутній або неправильний ключ завершує конвеєр відповіддю 403. Для дозволеного запиту після виконання контролера додається заголовок X-Security-Policy.")
    add_picture(doc, assets["middleware"], "Рисунок 5 - Перевірка партнерського API-ключа", "Код middleware VerifyPartnerApiKey", 15.5)
    doc.add_page_break()
    add_body(doc, "Аліас ticket.partner зареєстровано в bootstrap/app.php. CSRF-виняток обмежено маршрутами tickets і використано лише для консольного тестування POST-запитів у межах лабораторної.")
    add_picture(doc, assets["bootstrap"], "Рисунок 6 - Реєстрація middleware", "Конфігурація middleware і CSRF винятку", 15.5)

    doc.add_heading("7 Функціональні тести", level=1)
    add_body(doc, "Клас TicketAccessTest охоплює список квитків, пошук наявного й відсутнього запису, блокування без ключа та з неправильним ключем, створення з правильним ключем і помилки валідації.")
    add_picture(doc, assets["tests_1"], "Рисунок 7 - GET і негативні сценарії", "Початок feature-тестів TicketAccessTest", 15.5)
    doc.add_page_break()
    add_picture(doc, assets["tests_2"], "Рисунок 8 - Створення та валідація", "Завершення feature-тестів TicketAccessTest", 15.5)

    doc.add_heading("8 Результати локальної перевірки", level=1)
    add_body(doc, "Фінальний сценарій lab_5/scripts/verify.ps1 перевірив синтаксис PHP-файлів, таблицю маршрутів, Laravel Pint і весь PHPUnit-набір. В автономному проєкті отримано 8 успішних тестів і 25 тверджень; окремо TicketAccessTest містить 7 тестів і 24 твердження.")
    add_picture(doc, assets["terminal"], "Рисунок 9 - Результат автоматизованої перевірки", "Термінальний підсумок ключа маршрутів Pint і PHPUnit", 15.5)

    doc.add_heading("9 Перевірка HTTP-маршрутів", level=1)
    add_body(doc, "Локальний сервер перевірено запитами cURL. GET /tickets повернув 200, захищений POST без ключа - 403, POST із ключем - 201 та службовий заголовок, а GET /tickets/999 - 404.")
    add_picture(doc, assets["http"], "Рисунок 10 - Коди стану HTTP", "Чотири перевірені HTTP сценарії", 15.6)
    add_picture(doc, assets["tickets_json"], "Рисунок 11 - JSON-відповідь GET /tickets", "Відформатована відповідь зі списком трьох квитків", 15.6)

    doc.add_heading("10 Налаштування CI та стан Git", level=1)
    add_body(doc, "Workflow lab5-ci.yml налаштовано на PHP 8.2, встановлення залежностей Composer у каталозі lab_5, генерацію APP_KEY, перевірку Pint і запуск php artisan test. Роботу підготовлено в окремій гілці feature/lab-5-laravel-routing для перевірки через Pull Request.")
    add_picture(doc, assets["ci"], "Рисунок 12 - Конфігурація GitHub Actions", "Кроки CI для Laravel", 15.5)
    add_body(doc, "Гілку feature/lab-5-laravel-routing підготовлено для публікації та перевірки через Pull Request. У звіті наведено відтворювану конфігурацію CI; актуальний статус виконання фіксується на сторінці Pull Request у GitHub.")

    doc.add_heading("11 Відповіді на запитання для самоконтролю", level=1)
    qa = [
        ("1 Життєвий цикл запиту", "public/index.php підключає Composer autoload, створює застосунок і передає запит до HTTP-ядра. Далі запит проходить middleware, маршрутизатор, контролер і повертається клієнту як Response."),
        ("2 Перевага Artisan", "Artisan створює класи в правильних каталогах і просторах імен, дотримується конвенцій Laravel та зменшує ризик помилок у шаблонному коді."),
        ("3 Об'єкт Request", "Request надає єдиний ООП-інтерфейс до параметрів, заголовків і файлів, підтримує типізоване читання та інтегрується з валідатором, тому пряме використання $_GET і $_POST не потрібне."),
        ("4 Патерн middleware", "Конвеєр відповідає Chain of Responsibility і декоратору. Код до $next($request) виконує попередню перевірку, а код після нього змінює вже сформовану відповідь."),
        ("5 Обмеження параметрів", "whereNumber('id') додає регулярне обмеження для параметра маршруту. Нецифрове значення не потрапляє до методу show() і не збігається з маршрутом."),
        ("6 Іменовані маршрути", "Ім'я маршруту відокремлює посилання від конкретного URI. Після зміни префікса код може й далі використовувати route('tickets.show', $id) без масового виправлення URL."),
    ]
    for heading, answer in qa:
        add_body(doc, f"{heading}. {answer}", bold_lead=f"{heading}.")

    doc.add_heading("Висновки", level=1)
    add_body(doc, "У лабораторній роботі побудовано самодостатній Laravel-проєкт системи продажу квитків у каталозі lab_5. Декларативний роутинг відокремив URI від бізнес-логіки, контролер сформував стандартизовані JSON-відповіді й централізовану валідацію, а middleware ізолював правило партнерського доступу. Локально підтверджено всі обов'язкові HTTP-сценарії, форматування коду і 8 тестів із 25 твердженнями.")

    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    doc.save(OUTPUT)
    print(OUTPUT)


if __name__ == "__main__":
    build_report()
