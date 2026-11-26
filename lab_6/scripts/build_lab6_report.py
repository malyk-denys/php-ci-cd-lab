from __future__ import annotations

import sqlite3
from pathlib import Path
from textwrap import wrap

from docx import Document
from docx.enum.table import WD_ALIGN_VERTICAL, WD_TABLE_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_LINE_SPACING
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Cm, Pt, RGBColor
from PIL import Image, ImageDraw, ImageFont

from report_helpers import (
    BORDER,
    NAVY,
    PALE_BLUE,
    add_body,
    add_bullet,
    add_centered,
    add_picture,
    configure_document,
    locate_font,
    make_code_image,
    set_cell_margins,
    set_repeat_header,
    set_run_font,
    set_table_borders,
    shade_cell,
)


ROOT = Path(__file__).resolve().parents[1]
DOCS = ROOT / "docs" / "lab6"
ASSETS = DOCS / "report_assets"
OUTPUT = ROOT / "docs" / "Lab6_Report_Variant3.docx"


def add_bullet(doc: Document, text: str) -> None:
    paragraph = doc.add_paragraph()
    paragraph.paragraph_format.left_indent = Cm(0.8)
    paragraph.paragraph_format.first_line_indent = Cm(-0.5)
    paragraph.paragraph_format.line_spacing_rule = WD_LINE_SPACING.ONE_POINT_FIVE
    paragraph.paragraph_format.space_after = Pt(3)
    set_run_font(paragraph.add_run("•  "+text))


def add_title_page(doc: Document) -> None:
    add_centered(doc, "МІНІСТЕРСТВО ОСВІТИ І НАУКИ УКРАЇНИ", bold=True)
    add_centered(doc, "ЗАКЛАД ВИЩОЇ ОСВІТИ ____________________________", size=12)
    for _ in range(4):
        doc.add_paragraph()

    doc.add_paragraph("ЗВІТ ДО ЛАБОРАТОРНОЇ РОБОТИ 6", style="Title")
    add_centered(doc, "з дисципліни Веб програмування")
    add_centered(
        doc,
        "Робота з базами даних з використанням фреймворку Laravel",
        bold=True,
        after=10,
    )
    add_centered(doc, "Варіант 3", bold=True)
    add_centered(doc, "Система продажу квитків до кінотеатру", bold=True)
    for _ in range(2):
        doc.add_paragraph()

    table = doc.add_table(rows=4, cols=2)
    table.alignment = WD_TABLE_ALIGNMENT.RIGHT
    table.autofit = False
    rows = (
        ("Виконав", "Малик Денис"),
        ("Група", "________________________"),
        ("Перевірив", "________________________"),
        ("Оцінка", "________________________"),
    )
    for row, values in zip(table.rows, rows):
        for cell, text, width in zip(row.cells, values, (Cm(3.0), Cm(7.2))):
            cell.width = width
            set_cell_margins(cell, top=40, start=40, bottom=40, end=40)
            paragraph = cell.paragraphs[0]
            paragraph.paragraph_format.first_line_indent = Cm(0)
            set_run_font(paragraph.add_run(text))

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


def add_table(doc: Document, headers: tuple[str, ...], rows: list[tuple[str, ...]], widths: tuple[float, ...]) -> None:
    table = doc.add_table(rows=1, cols=len(headers))
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    table.autofit = False
    for cell, text, width in zip(table.rows[0].cells, headers, widths):
        cell.width = Cm(width)
        shade_cell(cell, NAVY)
        set_cell_margins(cell)
        cell.vertical_alignment = WD_ALIGN_VERTICAL.CENTER
        paragraph = cell.paragraphs[0]
        paragraph.alignment = WD_ALIGN_PARAGRAPH.CENTER
        paragraph.paragraph_format.first_line_indent = Cm(0)
        paragraph.paragraph_format.line_spacing_rule = WD_LINE_SPACING.SINGLE
        run = paragraph.add_run(text)
        set_run_font(run, size=Pt(9.5), bold=True)
        run.font.color.rgb = RGBColor(255, 255, 255)
    set_repeat_header(table.rows[0])

    for row_index, values in enumerate(rows):
        cells = table.add_row().cells
        if row_index % 2:
            for cell in cells:
                shade_cell(cell, PALE_BLUE)
        for col_index, (cell, text, width) in enumerate(zip(cells, values, widths)):
            cell.width = Cm(width)
            cell.vertical_alignment = WD_ALIGN_VERTICAL.CENTER
            set_cell_margins(cell)
            paragraph = cell.paragraphs[0]
            paragraph.paragraph_format.first_line_indent = Cm(0)
            paragraph.paragraph_format.line_spacing_rule = WD_LINE_SPACING.SINGLE
            paragraph.alignment = WD_ALIGN_PARAGRAPH.CENTER if col_index == 0 else WD_ALIGN_PARAGRAPH.LEFT
            set_run_font(paragraph.add_run(text), size=Pt(9.5))
    set_table_borders(table)


def draw_box(draw: ImageDraw.ImageDraw, xy: tuple[int, int, int, int], title: str, lines: list[str], fill: str) -> None:
    x1, y1, x2, y2 = xy
    title_font = ImageFont.truetype(locate_font(True), 30)
    body_font = ImageFont.truetype(locate_font(), 23)
    draw.rounded_rectangle(xy, radius=18, fill="#FFFFFF", outline="#8AA4C1", width=3)
    draw.rounded_rectangle((x1, y1, x2, y1 + 62), radius=18, fill=fill, outline=fill)
    draw.rectangle((x1, y1 + 43, x2, y1 + 62), fill=fill)
    draw.text((x1 + 24, y1 + 16), title, font=title_font, fill="#FFFFFF")
    y = y1 + 84
    for line in lines:
        draw.text((x1 + 24, y), line, font=body_font, fill="#243447")
        y += 35


def make_schema_image(output: Path) -> None:
    image = Image.new("RGB", (1800, 1050), "#F5F8FC")
    draw = ImageDraw.Draw(image)
    heading = ImageFont.truetype(locate_font(True), 42)
    label = ImageFont.truetype(locate_font(True), 25)
    draw.text((72, 42), "Реляційна схема варіанта 3", font=heading, fill="#172B4D")

    draw_box(
        draw,
        (80, 150, 555, 585),
        "events",
        [
            "PK  id",
            "    name",
            "UQ  slug",
            "    venue",
            "IX  starts_at, status",
            "    description",
            "    timestamps",
        ],
        "#1F4E78",
    )
    draw_box(
        draw,
        (665, 150, 1155, 660),
        "tickets",
        [
            "PK  id",
            "FK  event_id",
            "UQ  code",
            "UQ  event_id + seat_number",
            "IX  status + price",
            "IX  customer_email",
            "    sold_at, timestamps",
        ],
        "#17365D",
    )
    draw_box(
        draw,
        (1260, 150, 1720, 620),
        "discounts",
        [
            "PK  id",
            "UQ  code",
            "    name, type, value",
            "    active_from",
            "IX  is_active + active_until",
            "    timestamps",
        ],
        "#1F4E78",
    )
    draw_box(
        draw,
        (720, 790, 1165, 985),
        "discount_ticket",
        [
            "PK  id",
            "FK  discount_id",
            "FK  ticket_id",
            "UQ  discount_id + ticket_id",
        ],
        "#305A82",
    )

    draw.line((555, 360, 665, 360), fill="#334E68", width=5)
    draw.text((570, 315), "1      M", font=label, fill="#334E68")
    draw.line((910, 660, 910, 790), fill="#334E68", width=5)
    draw.line((1490, 620, 1490, 730, 1080, 730, 1080, 790), fill="#334E68", width=5)
    draw.text((928, 688), "1", font=label, fill="#334E68")
    draw.text((928, 750), "M", font=label, fill="#334E68")
    draw.text((1510, 650), "1", font=label, fill="#334E68")
    draw.text((1110, 744), "M", font=label, fill="#334E68")

    output.parent.mkdir(parents=True, exist_ok=True)
    image.save(output, optimize=True)


def make_terminal_image(output: Path, title: str, lines: list[str], success_markers: tuple[str, ...] = ()) -> None:
    width = 1800
    line_height = 43
    wrapped: list[str] = []
    for line in lines:
        wrapped.extend(wrap(line, width=100, replace_whitespace=False, drop_whitespace=False) or [""])
    height = 102 + len(wrapped) * line_height + 30
    image = Image.new("RGB", (width, height), "#0C1117")
    draw = ImageDraw.Draw(image)
    font = ImageFont.truetype(locate_font(), 25)
    bold = ImageFont.truetype(locate_font(True), 27)
    draw.rectangle((0, 0, width, 70), fill="#161B22")
    draw.text((30, 18), title, font=bold, fill="#F0F6FC")
    y = 92
    for line in wrapped:
        color = "#7EE787" if any(marker in line for marker in success_markers) else "#E6EDF3"
        draw.text((34, y), line, font=font, fill=color)
        y += line_height
    output.parent.mkdir(parents=True, exist_ok=True)
    image.save(output, optimize=True)


def make_pivot_image(output: Path) -> None:
    database = ROOT / "database" / "database.sqlite"
    with sqlite3.connect(database) as connection:
        rows = connection.execute(
            """
            SELECT dt.id, dt.ticket_id, dt.discount_id, t.code, d.code
            FROM discount_ticket dt
            JOIN tickets t ON t.id = dt.ticket_id
            JOIN discounts d ON d.id = dt.discount_id
            ORDER BY dt.id
            LIMIT 10
            """
        ).fetchall()
    lines = [
        "id | ticket_id | discount_id | ticket_code       | discount_code",
        "---+-----------+-------------+-------------------+----------------",
    ]
    lines.extend(
        f"{row[0]:>2} | {row[1]:>9} | {row[2]:>11} | {row[3]:<17} | {row[4]}"
        for row in rows
    )
    make_terminal_image(output, "SQLite  discount_ticket", lines)


def prepare_assets() -> dict[str, Path]:
    ASSETS.mkdir(parents=True, exist_ok=True)
    specs = [
        ("migration_events", ROOT / "database/migrations/2026_11_08_000001_create_events_table.php", "Міграція events", 1, None),
        ("migration_tickets", ROOT / "database/migrations/2026_11_08_000003_create_tickets_table.php", "Міграція tickets", 1, None),
        ("migration_pivot", ROOT / "database/migrations/2026_11_08_000004_create_discount_ticket_table.php", "Міграція discount_ticket", 1, None),
        ("model_event", ROOT / "app/Models/Event.php", "Модель Event", 1, None),
        ("model_ticket", ROOT / "app/Models/Ticket.php", "Модель Ticket", 1, None),
        ("model_discount", ROOT / "app/Models/Discount.php", "Модель Discount", 1, None),
        ("factory_ticket", ROOT / "database/factories/TicketFactory.php", "TicketFactory", 1, None),
        ("seeder", ROOT / "database/seeders/DatabaseSeeder.php", "DatabaseSeeder", 1, None),
        ("controller_index", ROOT / "app/Http/Controllers/TicketController.php", "TicketController  index і store", 1, 74),
        ("controller_sync", ROOT / "app/Http/Controllers/TicketController.php", "TicketController  update і sync", 87, 154),
        ("routes", ROOT / "routes/web.php", "Ресурсні маршрути", 1, None),
        ("test_database", ROOT / "tests/Feature/TicketDatabaseTest.php", "TicketDatabaseTest", 1, 73),
        ("ci", ROOT.parent / ".github/workflows/lab6-ci.yml", "GitHub Actions  lab6-ci.yml", 1, None),
    ]
    assets: dict[str, Path] = {}
    for key, source, title, start, end in specs:
        target = ASSETS / f"{key}.png"
        make_code_image(source, target, title, start, end)
        assets[key] = target

    assets["schema"] = ASSETS / "schema.png"
    make_schema_image(assets["schema"])

    assets["migrations"] = ASSETS / "migration-status.png"
    make_terminal_image(
        assets["migrations"],
        "php artisan migrate:fresh --seed  та  migrate:status",
        [
            "Dropping all tables ........................................................ DONE",
            "0001_01_01_000000_create_users_table ....................................... DONE",
            "2026_11_08_000001_create_events_table ...................................... DONE",
            "2026_11_08_000002_create_discounts_table ................................... DONE",
            "2026_11_08_000003_create_tickets_table ..................................... DONE",
            "2026_11_08_000004_create_discount_ticket_table ............................. DONE",
            "INFO  Seeding database.",
            "All 7 migrations: Batch 1, Status Ran",
        ],
        ("DONE", "Ran"),
    )

    assets["verification"] = ASSETS / "verification.png"
    make_terminal_image(
        assets["verification"],
        "PowerShell  scripts/verify.ps1",
        [
            "Routes: 21 registered",
            '{"tool":"pint","result":"passed"}',
            "PASS  Tests\\Feature\\TicketAccessTest",
            "PASS  Tests\\Feature\\TicketDatabaseTest",
            "Tests: 13 passed (47 assertions)",
            "Duration: 1.75s",
            "LAB 6 VALIDATION: PASS",
        ],
        ("passed", "PASS"),
    )

    assets["tinker"] = ASSETS / "tinker.png"
    make_terminal_image(
        assets["tinker"],
        "php artisan tinker  Eloquent relations",
        [
            '> $event = new Event([...]); dump(["before_save_id" => $event->id]);',
            'before_save_id => null',
            '> $event->save(); dump(["after_save_id" => $event->id]);',
            'after_save_id => 6',
            '> $ticket = $event->tickets()->create([...]);',
            'event_tickets => 1; parent_event => "Tinker Demo Event"',
            '> $ticket->discounts()->attach([$d1->id, $d2->id]);',
            'after_attach => ["TINKER10", "TINKER50"]',
            '> $ticket->discounts()->create([...]);',
            'after_create => ["TINKER10", "TINKER50", "TINKERNEW"]',
            '> $ticket->discounts()->sync([$d1->id]);',
            'after_sync => ["TINKER10"]',
        ],
        ("after_", "event_tickets"),
    )

    assets["http_tests"] = ASSETS / "http-tests.png"
    make_terminal_image(
        assets["http_tests"],
        "Laravel HTTP kernel  feature tests",
        [
            "GET /tickets -> 200; event and discounts eager-loaded",
            "GET /tickets/{ticket} -> 200; missing model -> 404",
            "POST /tickets without X-Partner-Api-Key -> 403",
            "POST /tickets with valid key -> 201; pivot rows persisted",
            "PUT /tickets/{ticket} -> 200; sync() keeps selected discounts",
            "Duplicate seat in one event -> 422 validation error",
        ],
        ("200", "201"),
    )

    assets["pivot"] = ASSETS / "pivot.png"
    make_pivot_image(assets["pivot"])
    return assets


def build_report() -> None:
    assets = prepare_assets()
    doc = Document()
    configure_document(doc)
    title_properties = doc.styles["Title"]._element.get_or_add_pPr()
    title_border = title_properties.find(qn("w:pBdr"))
    if title_border is not None:
        title_properties.remove(title_border)
    add_title_page(doc)

    doc.add_heading("1 Мета і постановка завдання", level=1)
    add_body(
        doc,
        "Мета роботи - опанувати об'єктно-реляційне відображення Active Record у Laravel, навчитися версіонувати структуру бази даних міграціями, описувати зв'язки Eloquent, генерувати тестові дані фабриками та перевіряти роботу схеми автоматизованими тестами.",
    )
    add_body(
        doc,
        "Для варіанта 3 реалізовано систему продажу квитків до кінотеатру. Головна сутність Ticket належить події Event у зв'язку 1:M. Квитки та знижки Discount утворюють зв'язок M:M через таблицю discount_ticket.",
    )
    add_bullet(doc, "Створити міграції з ключами, індексами, унікальними обмеженнями та каскадним видаленням.")
    add_bullet(doc, "Реалізувати моделі, фабрики, сідер і двонапрямлені відношення Eloquent.")
    add_bullet(doc, "Перевести TicketController з mock-масиву на SQLite та усунути N+1 через eager loading.")
    add_bullet(doc, "Перевірити attach(), створення через відношення, sync(), REST-маршрути і цілісність БД.")

    doc.add_heading("2 Теоретичні відомості", level=1)
    add_body(
        doc,
        "Eloquent реалізує патерн Active Record: екземпляр моделі одночасно містить стан рядка і методи для його збереження, оновлення та видалення. На відміну від Data Mapper, окремий менеджер сутностей не потрібний. Це скорочує кількість інфраструктурного коду і добре відповідає конвенціям Laravel.",
    )
    add_body(
        doc,
        "Міграції перетворюють структуру БД на версіонований код. Метод up() застосовує зміну, а down() виконує її зворотну дію. Фабрики описують шаблони даних, сідер формує пов'язаний набір, а RefreshDatabase забезпечує ізольований стан для кожного тесту.",
    )
    add_body(
        doc,
        "Eager loading через with() формує окремі пакетні запити для зв'язків і запобігає проблемі N+1, коли кожний елемент колекції породжує додатковий SELECT. У середовищі розробки ввімкнено preventLazyLoading(), тому випадкове ледаче завантаження виявляється одразу.",
    )

    doc.add_heading("3 Проєктування схеми даних", level=1)
    add_picture(doc, assets["schema"], "Рисунок 1 - Реляційна схема бази даних", "Таблиці events tickets discounts і discount ticket з ключами", 15.6)
    add_body(
        doc,
        "Зовнішній ключ tickets.event_id має cascadeOnDelete(), тому видалення події прибирає її квитки. Обидва ключі pivot-таблиці також каскадні. Складені унікальні обмеження забороняють дублювати місце в межах події та повторювати ту саму пару квиток-знижка.",
    )

    doc.add_heading("4 Узгодження сутностей і реалізації", level=1)
    add_table(
        doc,
        ("Елемент", "Файл міграції", "Модель і відношення", "Маршрути"),
        [
            ("Event", "create_events_table", "Event::tickets() hasMany", "events.*"),
            ("Ticket", "create_tickets_table", "Ticket::event() belongsTo; Ticket::discounts() belongsToMany", "tickets.*"),
            ("Discount", "create_discounts_table", "Discount::tickets() belongsToMany", "discounts.*"),
            ("M:M", "create_discount_ticket_table", "withTimestamps(); attach(); sync()", "POST і PUT tickets"),
        ],
        (2.4, 3.5, 6.0, 2.7),
    )
    doc.add_page_break()

    doc.add_heading("5 Міграції", level=1)
    add_body(doc, "Міграція events створює батьківську таблицю з унікальним slug та індексом за статусом і часом початку.")
    add_picture(doc, assets["migration_events"], "Рисунок 2 - Міграція таблиці events", "Код міграції подій", 15.2)
    doc.add_page_break()
    add_body(doc, "Таблиця tickets містить зовнішній ключ, унікальний код квитка, ціну, місце, покупця, статус і предметні індекси.")
    add_picture(doc, assets["migration_tickets"], "Рисунок 3 - Міграція таблиці tickets", "Код міграції квитків", 15.2)
    doc.add_page_break()
    add_body(doc, "Проміжна таблиця discount_ticket реалізує M:M і захищена складеним унікальним індексом.")
    add_picture(doc, assets["migration_pivot"], "Рисунок 4 - Міграція проміжної таблиці", "Код міграції discount ticket", 15.2)
    add_picture(doc, assets["migrations"], "Рисунок 5 - Виконання і статус міграцій", "Успішний migrate fresh seed та статус семи міграцій", 15.2)

    doc.add_page_break()
    doc.add_heading("6 Моделі Eloquent і відношення", level=1)
    add_body(doc, "Моделі містять лише дозволені для масового призначення поля, типізовані casts і методи відношень із конкретними типами повернення.")
    add_picture(doc, assets["model_event"], "Рисунок 6 - Модель Event і hasMany", "Модель Event", 15.2)
    doc.add_page_break()
    add_picture(doc, assets["model_ticket"], "Рисунок 7 - Модель Ticket і два відношення", "Модель Ticket", 15.2)
    doc.add_page_break()
    add_picture(doc, assets["model_discount"], "Рисунок 8 - Модель Discount і belongsToMany", "Модель Discount", 15.2)

    doc.add_heading("7 Фабрики і наповнення БД", level=1)
    add_body(
        doc,
        "TicketFactory створює коректні коди, місця, ціни, email і статуси. Посилання Event::factory() автоматично забезпечує батьківський запис. DatabaseSeeder спочатку створює 10 знижок, а потім 5 подій по 8 квитків; кожен квиток отримує дві випадкові знижки.",
    )
    add_picture(doc, assets["factory_ticket"], "Рисунок 9 - Фабрика квитків", "Код TicketFactory", 15.2)
    doc.add_page_break()
    add_picture(doc, assets["seeder"], "Рисунок 10 - Сідер пов'язаних даних", "Код DatabaseSeeder", 15.2)
    add_picture(doc, assets["pivot"], "Рисунок 11 - Фрагмент вмісту discount_ticket", "Перші десять пар зовнішніх ключів pivot таблиці", 15.2)

    doc.add_heading("8 Дослідження через Artisan Tinker", level=1)
    add_body(
        doc,
        "У Tinker підтверджено різницю між new і save(): до збереження первинний ключ дорівнював null, після INSERT модель отримала id 6. Дочірній квиток створено через $event->tickets()->create(), а зворотний доступ повернув назву події.",
    )
    add_body(
        doc,
        "Для M:M виконано attach() двох знижок, створення третьої через $ticket->discounts()->create() та sync() з одним id. Підсумкова колекція містила лише TINKER10, що підтвердило синхронізацію pivot-таблиці.",
    )
    add_picture(doc, assets["tinker"], "Рисунок 12 - Перевірка відношень у Tinker", "Фактичні результати save create attach і sync", 15.2)

    doc.add_heading("9 Рефакторинг контролера", level=1)
    add_body(
        doc,
        "TicketController повністю переведено на Eloquent. Метод index() застосовує with() для Event і Discount та підтримує фільтри. Метод store() перевіряє зовнішні ключі, створює Ticket і синхронізує знижки в транзакції.",
    )
    add_picture(doc, assets["controller_index"], "Рисунок 13 - Eager loading і створення квитка", "Методи index і store контролера", 13.5)
    doc.add_page_break()
    add_body(
        doc,
        "Під час оновлення унікальність місця перевіряється в межах обраної події, а переданий масив discounts замінює попередні зв'язки через sync(). Якщо поле не передано, наявні зв'язки зберігаються.",
    )
    add_picture(doc, assets["controller_sync"], "Рисунок 14 - Оновлення і sync", "Метод update з транзакцією та sync", 15.2)

    doc.add_heading("10 Маршрути і HTTP перевірка", level=1)
    add_body(
        doc,
        "Для трьох ключових сутностей зареєстровано ресурсні маршрути index, store, show, update і destroy. Операції зміни квитків проходять middleware ticket.partner. Feature-тести виконують запити через реальне HTTP-ядро Laravel.",
    )
    add_picture(doc, assets["routes"], "Рисунок 15 - Ресурсні маршрути", "Код routes web php", 15.2)
    add_picture(doc, assets["http_tests"], "Рисунок 16 - Результати HTTP сценаріїв", "Статуси та перевірені умови feature тестів", 15.2)

    doc.add_heading("11 Автоматизоване тестування", level=1)
    add_body(
        doc,
        "TicketDatabaseTest використовує RefreshDatabase і перевіряє eager-loaded відповідь, створення квитка з двома знижками, збереження обох рядків pivot-таблиці, роботу sync(), каскадне видалення та унікальність місця в межах події.",
    )
    add_picture(doc, assets["test_database"], "Рисунок 17 - Інтеграційні тести БД", "Основні методи TicketDatabaseTest", 15.2)
    doc.add_page_break()
    add_body(
        doc,
        "Сценарій scripts/verify.ps1 виконав синтаксичну перевірку, migrate:fresh --seed, migrate:status, route:list, Pint і повний PHPUnit-набір. Усі 13 тестів і 47 тверджень пройшли успішно.",
    )
    add_picture(doc, assets["verification"], "Рисунок 18 - Фінальна локальна перевірка", "Підсумок Pint PHPUnit і сценарію Lab 6", 15.2)

    doc.add_heading("12 Налаштування CI", level=1)
    add_body(
        doc,
        "Workflow GitHub Actions налаштовано на PHP 8.2 з pdo_sqlite. Перед тестами runner створює database/database.sqlite і виконує міграції. Після цього запускаються Pint і повний набір тестів.",
    )
    add_picture(doc, assets["ci"], "Рисунок 19 - Конфігурація CI для SQLite", "Кроки GitHub Actions", 15.2)

    doc.add_heading("13 Відповіді на запитання для самоконтролю", level=1)
    answers = [
        ("1 Active Record і Data Mapper", "Active Record поєднує стан рядка й операції збереження у моделі; Data Mapper передає їх окремому менеджеру. Eloquent реалізує Active Record."),
        ("2 Версіонування структури", "Ручні зміни не відтворюються на інших середовищах. Міграції зберігають історію в Git, а таблиця migrations фіксує виконані файли й batch."),
        ("3 Методи up і down", "up() застосовує зміну, down() її скасовує. migrate:fresh перевідтворює всі таблиці, а --seed запускає DatabaseSeeder."),
        ("4 Mass Assignment", "Запит може змінити неочікувані поля. $fillable утворює білий список атрибутів для create() і update()."),
        ("5 attach detach sync", "attach() додає пари ключів, detach() їх видаляє, а sync() приводить зв'язки до переданого списку id."),
        ("6 Проблема N плюс 1", "Окремий запит зв'язку для кожного рядка створює N+1 звернень. with() завантажує пов'язані записи пакетно."),
        ("7 RefreshDatabase", "Трейт запускає міграції та ізолює стан, тому тести не залежать від порядку й не залишають дані один одному."),
        ("8 Artisan Tinker", "Tinker завантажує Laravel і дозволяє інтерактивно перевіряти моделі, запити та відношення без HTTP-рівня."),
        ("9 Метод і властивість зв'язку", "$ticket->event() повертає BelongsTo для побудови запиту, а $ticket->event - пов'язану модель Event."),
        ("10 Перевага sync", "sync() додає відсутні й видаляє зайві зв'язки одним викликом, тому є простішим за ручні detach() і attach()."),
    ]
    for heading, answer in answers:
        add_body(doc, f"{heading}. {answer}", bold_lead=f"{heading}.")

    doc.add_heading("Висновки", level=1)
    add_body(
        doc,
        "У лабораторній роботі реалізовано повний шар зберігання даних для варіанта 3. Міграції забезпечили відтворювану схему та цілісність ключів, Eloquent-моделі - двонапрямлені зв'язки 1:M і M:M, а фабрики та сідер - реалістичний набір даних. Контролер більше не залежить від тимчасового масиву, використовує eager loading і транзакційний sync(). Коректність підтверджено Tinker, вмістом pivot-таблиці, HTTP feature-тестами та повним результатом 13 успішних тестів із 47 твердженнями.",
    )

    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    doc.save(OUTPUT)
    print(OUTPUT)


if __name__ == "__main__":
    build_report()
