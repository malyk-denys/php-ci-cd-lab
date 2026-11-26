from __future__ import annotations

import json
from pathlib import Path

from docx import Document
from docx.enum.table import WD_ALIGN_VERTICAL, WD_TABLE_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_LINE_SPACING
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Cm, Pt, RGBColor

from report_helpers import (
    NAVY,
    PALE_BLUE,
    add_body,
    add_bullet,
    add_centered,
    add_picture,
    configure_document,
    make_code_image,
    set_cell_margins,
    set_repeat_header,
    set_run_font,
    set_table_borders,
    shade_cell,
)


ROOT = Path(__file__).resolve().parents[1]
DOCS = ROOT / "docs" / "lab7"
ASSETS = DOCS / "report_assets"
OUTPUT = DOCS / "Lab7_Report_Variant3.docx"


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


def add_title_page(doc: Document) -> None:
    add_centered(doc, "МІНІСТЕРСТВО ОСВІТИ І НАУКИ УКРАЇНИ", bold=True)
    add_centered(doc, "ЗАКЛАД ВИЩОЇ ОСВІТИ ____________________________", size=12)
    for _ in range(4):
        doc.add_paragraph()

    doc.add_paragraph("ЗВІТ ДО ЛАБОРАТОРНОЇ РОБОТИ 7", style="Title")
    add_centered(doc, "з дисципліни Веб програмування")
    add_centered(
        doc,
        "Представлення даних Blade компоненти та взаємодія через форми",
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


def prepare_code_assets() -> None:
    make_code_image(
        ROOT / "resources/views/layouts/app.blade.php",
        ASSETS / "code-layout.png",
        "resources/views/layouts/app.blade.php",
        1,
        70,
    )
    make_code_image(
        ROOT / "resources/views/components/form/input.blade.php",
        ASSETS / "code-input-component.png",
        "resources/views/components/form/input.blade.php",
        1,
        21,
    )
    make_code_image(
        ROOT / "routes/web.php",
        ASSETS / "code-routes.png",
        "routes/web.php",
        1,
        26,
    )
    make_code_image(
        ROOT / "app/Http/Controllers/TicketController.php",
        ASSETS / "code-store.png",
        "TicketController store",
        54,
        91,
    )
    make_code_image(
        ROOT / "app/Http/Controllers/TicketController.php",
        ASSETS / "code-update-destroy.png",
        "TicketController update and destroy",
        103,
        151,
    )
    make_code_image(
        ROOT / "app/Http/Controllers/TicketController.php",
        ASSETS / "code-validation.png",
        "TicketController validation rules",
        155,
        198,
    )
    make_code_image(
        ROOT / "tests/Feature/Lab7BladeViewTest.php",
        ASSETS / "code-feature-test.png",
        "tests/Feature/Lab7BladeViewTest.php",
        17,
        75,
    )


def add_intro(doc: Document) -> None:
    doc.add_heading("1 Мета та завдання роботи", level=1)
    add_body(
        doc,
        "Мета роботи - опанувати побудову серверного користувацького інтерфейсу засобами Blade у Laravel, створення спільного макета, повторно використовуваних компонентів і HTML-форм із серверною валідацією, CSRF-захистом та підміною HTTP-методів.",
    )
    add_body(doc, "Індивідуальний варіант: система продажу квитків до кінотеатру. Головною сутністю є Ticket.")
    add_bullet(doc, "реалізувати повний Blade CRUD для Ticket, Event і Discount;")
    add_bullet(doc, "відобразити зв’язок Event 1:M Ticket через список вибору;")
    add_bullet(doc, "відобразити зв’язок Ticket M:M Discount через групу чекбоксів;")
    add_bullet(doc, "застосувати eager loading, пагінацію, old(), @error, @csrf та Method Spoofing;")
    add_bullet(doc, "перевірити UI автоматизованими feature-тестами без порушення сумісності JSON API попередніх робіт.")

    doc.add_heading("2 Проєктування інтерфейсу", level=1)
    add_body(
        doc,
        "Система побудована за MVC. Контролери визначають формат відповіді за типом запиту: звичайний браузер отримує Blade HTML та перенаправлення з flash-повідомленнями, а клієнт з Accept application/json - попередній JSON API. Це дає змогу розширити застосунок без регресії лабораторних робіт 5 і 6.",
    )
    add_table(
        doc,
        ("Прецедент", "Сутності", "Blade-представлення", "Маршрут"),
        [
            ("Каталог квитків", "Ticket, Event, Discount", "tickets.index / show", "GET /tickets"),
            ("Створення квитка", "Ticket → Event; Ticket ↔ Discount", "tickets.create / _form", "POST /tickets"),
            ("Редагування квитка", "Ticket → Event; Ticket ↔ Discount", "tickets.edit / _form", "PUT /tickets/{id}"),
            ("Видалення квитка", "Ticket", "DELETE-форма у каталозі", "DELETE /tickets/{id}"),
            ("Керування подіями", "Event 1:M Ticket", "events.*", "Resource /events"),
            ("Керування знижками", "Discount M:M Ticket", "discounts.*", "Resource /discounts"),
        ],
        (4.1, 3.6, 4.6, 3.7),
    )


def add_implementation(doc: Document) -> None:
    doc.add_heading("3 Реалізація Blade інтерфейсу", level=1)
    doc.add_heading("3.1 Базовий макет", level=2)
    add_body(
        doc,
        "Макет містить Bootstrap 5 через CDN, адаптивну навігацію, спільний блок flash-повідомлень, секцію content та стилі сторінок. Активний пункт меню визначається через request()->routeIs().",
    )
    add_body(
        doc,
        "Потік SSR-запиту проходить від ресурсного маршруту до контролера, Eloquent-моделі та Blade-представлення. Спільні елементи винесено в layout і компоненти, а форми окремих сутностей використовують часткові шаблони, тому create та edit не дублюють розмітку.",
    )
    add_table(
        doc,
        ("Рівень", "Реалізація", "Призначення"),
        [
            ("Layout", "layouts/app.blade.php", "Навігація, Bootstrap, flash і секція content"),
            ("Компоненти", "components/form", "Поля, textarea та загальний список помилок"),
            ("Ticket views", "tickets/index, show, create, edit", "Головний CRUD зі зв’язками 1:M і M:M"),
            ("Event views", "events/*", "Керування подіями та пов’язаними квитками"),
            ("Discount views", "discounts/*", "Керування промокодами та пов’язаними квитками"),
            ("Контролери", "Ticket, Event, Discount", "Валідація, eager loading, sync і redirect"),
        ],
        (3.1, 5.3, 7.6),
    )
    add_picture(doc, ASSETS / "code-layout.png", "Рисунок 1 - Базовий макет Blade", "Лістинг базового макета Blade", 12.0)

    doc.add_heading("3.2 Компонент поля вводу", level=2)
    add_body(
        doc,
        "Анонімний компонент приймає ім’я, підпис, тип, значення та допоміжний текст. Він відновлює введення через old(), позначає невалідне поле класом is-invalid і показує повідомлення директивою @error.",
    )
    add_picture(doc, ASSETS / "code-input-component.png", "Рисунок 2 - Компонент поля вводу", "Лістинг Blade-компонента поля вводу", 15.0)

    doc.add_heading("3.3 Ресурсні маршрути", level=2)
    add_body(doc, "Для трьох сутностей зареєстровано повні ресурсні маршрути. Операції JSON API для квитків і надалі захищає партнерський middleware, тоді як браузерні форми проходять стандартну CSRF-перевірку.")
    add_picture(doc, ASSETS / "code-routes.png", "Рисунок 3 - Маршрути вебзастосунку", "Лістинг ресурсних маршрутів", 11.0)


def add_ui_walkthrough(doc: Document) -> None:
    doc.add_heading("4 Результат роботи користувацького інтерфейсу", level=1)
    add_body(doc, "Нижче наведено скріншоти реальних Blade-сторінок із даними локальної SQLite-бази та станами сесії, які також перевіряються HTTP feature-тестами.")
    add_picture(doc, ASSETS / "01-home.png", "Рисунок 4 - Головна сторінка та навігація", "Головна сторінка CinemaDesk", 15.8)
    add_picture(doc, ASSETS / "02-ticket-catalog.png", "Рисунок 5 - Каталог квитків із пов’язаними даними", "Каталог квитків із подіями", 15.8)
    add_picture(doc, ASSETS / "03-ticket-create.png", "Рисунок 6 - Форма створення з відкритим списком і вибраними M:M зв’язками", "Форма створення квитка", 15.8)
    add_picture(doc, ASSETS / "04-ticket-validation.png", "Рисунок 7 - Серверні помилки валідації", "Форма квитка з помилками", 15.8)
    doc.add_page_break()
    add_picture(doc, ASSETS / "05-ticket-edit.png", "Рисунок 8 - Форма редагування з даними бази", "Заповнена форма редагування квитка", 15.8)
    add_picture(doc, ASSETS / "06-ticket-success.png", "Рисунок 9 - Каталог після збереження з flash-повідомленням", "Успішне оновлення квитка", 15.8)


def add_controller_and_tests(doc: Document) -> None:
    doc.add_heading("5 Обробка форм у контролері", level=1)
    add_body(
        doc,
        "Метод store валідовує поля, створює Ticket у транзакції та синхронізує знижки через sync(). Для браузера повертається redirect()->route() з повідомленням успіху, а для API - JSON зі статусом 201.",
    )
    add_picture(doc, ASSETS / "code-store.png", "Рисунок 10 - Створення квитка", "Лістинг методу store", 14.5)
    add_body(
        doc,
        "Метод update повторно використовує правила, ігнорує власний запис у Rule::unique(), оновлює pivot-таблицю та повертає flash-повідомлення. destroy видаляє модель і виконує перенаправлення до каталогу.",
    )
    add_picture(doc, ASSETS / "code-update-destroy.png", "Рисунок 11 - Оновлення та видалення квитка", "Лістинг методів update і destroy", 10.5)
    add_picture(doc, ASSETS / "code-validation.png", "Рисунок 12 - Правила серверної валідації", "Лістинг правил валідації Ticket", 10.5)

    doc.add_heading("6 Автоматизоване тестування", level=1)
    add_body(
        doc,
        "Feature-тест перевіряє HTML-каталог з eager-loaded зв’язками, наявність CSRF і Method Spoofing, повернення помилок у сесію, повний браузерний CRUD та доступність сторінок Event і Discount.",
    )
    add_picture(doc, ASSETS / "code-feature-test.png", "Рисунок 13 - Фрагмент Lab7BladeViewTest", "Лістинг feature-тесту Blade UI", 9.3)
    add_picture(doc, ASSETS / "07-tests.png", "Рисунок 14 - Успішна повна перевірка", "Результат запуску тестів лабораторної 7", 13.5)
    add_body(doc, "Підсумок перевірки самодостатнього проєкту lab_7: 18 тестів, 91 твердження, 24 вебмаршрути; компіляція Blade та Pint завершилися успішно.")


def add_github_evidence(doc: Document) -> None:
    doc.add_heading("7 Інтеграція з GitHub", level=1)
    evidence_path = DOCS / "github_evidence.json"
    evidence = json.loads(evidence_path.read_text(encoding="utf-8"))
    add_body(
        doc,
        f"Роботу оформлено через Issue #{evidence['issue_number']} і гілку {evidence['branch']}. "
        f"Pull Request #{evidence['pr_number']} пройшов автоматичну перевірку Lab 7 CI та був об’єднаний з main. "
        "Після злиття Issue закрито, а завдання на Kanban-дошці переведено у стан Done.",
    )
    add_picture(doc, ASSETS / "08-github-issue.png", "Рисунок 15 - Issue лабораторної роботи 7", "Відкрите GitHub Issue лабораторної роботи 7", 15.8)
    add_picture(doc, ASSETS / "09-github-ci.png", "Рисунок 16 - Успішна перевірка Lab 7 CI", "Успішний GitHub Actions workflow лабораторної роботи 7", 15.8)
    add_picture(doc, ASSETS / "10-github-pr.png", "Рисунок 17 - Об’єднаний Pull Request", "Об’єднаний Pull Request лабораторної роботи 7", 15.8)
    add_picture(doc, ASSETS / "11-github-kanban.png", "Рисунок 18 - Завершене завдання на Kanban-дошці", "Issue лабораторної роботи 7 у колонці Done", 15.8)


def add_conclusion(doc: Document) -> None:
    doc.add_heading("8 Висновки", level=1)
    add_body(
        doc,
        "У лабораторній роботі створено повний серверний вебінтерфейс системи продажу квитків. Успадкування макета усунуло дублювання структури сторінок, а Blade-компоненти уніфікували поля форм і показ помилок. Зв’язки 1:M та M:M відображено зрозумілими елементами select і checkbox, а eager loading запобігає надлишковим запитам під час побудови каталогу.",
    )
    add_body(
        doc,
        "CSRF-токени захищають запити зміни стану, Method Spoofing забезпечує семантичні PUT і DELETE, а серверна валідація разом з old() формує передбачуваний сценарій виправлення помилок. Повний набір із 18 тестів підтвердив працездатність нового UI та відсутність регресії JSON API попередніх лабораторних робіт.",
    )


def build_report() -> None:
    prepare_code_assets()
    doc = Document()
    configure_document(doc)
    title_properties = doc.styles["Title"]._element.get_or_add_pPr()
    title_border = title_properties.find(qn("w:pBdr"))
    if title_border is not None:
        title_properties.remove(title_border)
    add_title_page(doc)
    add_intro(doc)
    add_implementation(doc)
    add_ui_walkthrough(doc)
    add_controller_and_tests(doc)
    add_github_evidence(doc)
    add_conclusion(doc)
    doc.core_properties.title = "Лабораторна робота 7 Представлення даних Blade"
    doc.core_properties.subject = "Веб програмування Laravel Blade SSR"
    doc.core_properties.author = "Малик Денис"
    doc.save(OUTPUT)
    print(f"Saved {OUTPUT}")


if __name__ == "__main__":
    build_report()
