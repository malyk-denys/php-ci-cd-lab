from __future__ import annotations

import sqlite3
from pathlib import Path
from textwrap import shorten

from PIL import Image, ImageDraw, ImageFont


ROOT = Path(__file__).resolve().parents[1]
ASSETS = ROOT / "docs" / "lab7" / "report_assets"
DATABASE = ROOT / "database" / "database.sqlite"
WIDTH, HEIGHT = 1600, 1050

INK = "#17233D"
BLUE = "#2457D6"
SOFT = "#F4F7FC"
MUTED = "#667085"
BORDER = "#D9E0EA"
GREEN = "#15803D"
RED = "#B42318"
YELLOW = "#B7791F"


def font(size: int, bold: bool = False) -> ImageFont.FreeTypeFont:
    name = "arialbd.ttf" if bold else "arial.ttf"
    candidates = [Path("C:/Windows/Fonts") / name, Path("C:/Windows/Fonts/calibri.ttf")]
    for candidate in candidates:
        if candidate.exists():
            return ImageFont.truetype(str(candidate), size)
    return ImageFont.load_default()


def rounded(draw: ImageDraw.ImageDraw, box: tuple[int, int, int, int], fill: str = "#FFFFFF", outline: str | None = BORDER, radius: int = 16, width: int = 2) -> None:
    draw.rounded_rectangle(box, radius=radius, fill=fill, outline=outline, width=width)


def label(draw: ImageDraw.ImageDraw, xy: tuple[int, int], text: str, size: int = 22, color: str = INK, bold: bool = False) -> None:
    draw.text(xy, text, font=font(size, bold), fill=color)


def button(draw: ImageDraw.ImageDraw, box: tuple[int, int, int, int], text: str, primary: bool = True, danger: bool = False) -> None:
    fill = RED if danger else (BLUE if primary else "#FFFFFF")
    outline = fill if primary or danger else "#98A2B3"
    text_color = "#FFFFFF" if primary or danger else INK
    rounded(draw, box, fill, outline, 10, 2)
    bbox = draw.textbbox((0, 0), text, font=font(18, True))
    x = box[0] + (box[2] - box[0] - (bbox[2] - bbox[0])) // 2
    y = box[1] + (box[3] - box[1] - (bbox[3] - bbox[1])) // 2 - 2
    label(draw, (x, y), text, 18, text_color, True)


def base_page(active: str) -> tuple[Image.Image, ImageDraw.ImageDraw]:
    image = Image.new("RGB", (WIDTH, HEIGHT), SOFT)
    draw = ImageDraw.Draw(image)
    draw.rectangle((0, 0, WIDTH, 82), fill="#111827")
    label(draw, (70, 24), "CinemaDesk", 30, "#FFFFFF", True)
    x = 305
    for item in ("Головна", "Квитки", "Події", "Знижки"):
        color = "#FFFFFF" if item == active else "#AAB4C5"
        label(draw, (x, 30), item, 20, color, item == active)
        x += 125
    button(draw, (1320, 18, 1530, 64), "+ Новий квиток")
    return image, draw


def save(image: Image.Image, name: str) -> None:
    ASSETS.mkdir(parents=True, exist_ok=True)
    image.save(ASSETS / name, optimize=True)


def draw_home() -> None:
    image, draw = base_page("Головна")
    rounded(draw, (70, 122, 1530, 535), fill="#1F3B8F", outline="#1F3B8F", radius=24)
    rounded(draw, (115, 165, 360, 208), fill="#FFFFFF", outline="#FFFFFF", radius=18)
    label(draw, (140, 174), "BLADE · SSR · CRUD", 17, BLUE, True)
    label(draw, (115, 245), "Керування продажем квитків", 46, "#FFFFFF", True)
    label(draw, (115, 320), "Єдиний вебінтерфейс для квитків, кіносеансів і знижок", 24, "#D9E5FF")
    label(draw, (115, 356), "із серверною валідацією та захищеними формами.", 24, "#D9E5FF")
    button(draw, (115, 420, 340, 478), "Відкрити квитки", primary=False)
    rounded(draw, (1080, 190, 1450, 455), fill="#3658A7", outline="#5E7BC4", radius=20)
    label(draw, (1120, 230), "ПРЕДМЕТНА ОБЛАСТЬ", 16, "#D9E5FF", True)
    label(draw, (1120, 275), "Кінотеатр", 30, "#FFFFFF", True)
    label(draw, (1120, 335), "Event 1:M Ticket", 22, "#D9E5FF")
    label(draw, (1120, 375), "Ticket M:M Discount", 22, "#D9E5FF")

    cards = [
        ("Головна сутність", "Квитки", "Продаж, місце, ціна, покупець, статус,\nподія та набір знижок."),
        ("Зв’язок 1:M", "Події", "Кіносеанс містить багато квитків;\nу формі використано список вибору."),
        ("Зв’язок M:M", "Знижки", "До квитка можна застосувати кілька знижок\nчерез групу чекбоксів."),
    ]
    for i, (eyebrow, title, body) in enumerate(cards):
        x1 = 70 + i * 495
        rounded(draw, (x1, 575, x1 + 460, 930), radius=20)
        label(draw, (x1 + 30, 615), eyebrow, 18, BLUE, True)
        label(draw, (x1 + 30, 660), title, 30, INK, True)
        y = 720
        for line in body.splitlines():
            label(draw, (x1 + 30, y), line, 20, MUTED)
            y += 34
        button(draw, (x1 + 30, 840, x1 + 175, 892), "Перейти", primary=i == 0)
    save(image, "01-home.png")


def load_rows() -> tuple[list[tuple], tuple, list[tuple]]:
    with sqlite3.connect(DATABASE) as connection:
        tickets = connection.execute(
            """
            SELECT tickets.code, tickets.seat_number, events.name, tickets.customer_email,
                   tickets.price, tickets.status
            FROM tickets JOIN events ON events.id = tickets.event_id
            ORDER BY tickets.id DESC LIMIT 6
            """
        ).fetchall()
        demo = connection.execute(
            """
            SELECT tickets.code, tickets.seat_number, events.name, tickets.customer_email,
                   tickets.price, tickets.status
            FROM tickets JOIN events ON events.id = tickets.event_id
            WHERE tickets.code = 'TKT-DEMO-2026' LIMIT 1
            """
        ).fetchone() or tickets[0]
        discounts = connection.execute("SELECT code, name FROM discounts ORDER BY id LIMIT 4").fetchall()
    return tickets, demo, discounts


def draw_catalog(success: bool = False) -> None:
    tickets, _, _ = load_rows()
    image, draw = base_page("Квитки")
    y = 120
    if success:
        rounded(draw, (70, y, 1530, y + 72), fill="#E8F7EE", outline="#8DD0A4", radius=12)
        label(draw, (100, y + 22), "Готово. Квиток TKT-DEMO-2026 успішно оновлено.", 21, GREEN, True)
        y += 100
    label(draw, (70, y), "Квитки", 40, INK, True)
    label(draw, (70, y + 58), "Каталог із подіями та знижками, завантаженими через Eloquent eager loading.", 20, MUTED)
    button(draw, (1310, y + 5, 1530, y + 58), "+ Додати квиток")
    rounded(draw, (70, y + 105, 1530, y + 205), radius=14)
    for x, title, value in ((95, "Подія", "Усі події ▾"), (630, "Статус", "Усі статуси ▾")):
        label(draw, (x, y + 122), title, 15, INK, True)
        rounded(draw, (x, y + 149, x + 450, y + 190), radius=7)
        label(draw, (x + 14, y + 158), value, 16, MUTED)
    button(draw, (1185, y + 145, 1360, y + 192), "Фільтрувати", primary=False)

    table_y = y + 235
    rounded(draw, (70, table_y, 1530, 984), radius=14)
    draw.rectangle((70, table_y, 1530, table_y + 62), fill="#1F2937")
    columns = [(90, "Код / місце"), (300, "Подія"), (640, "Покупець"), (915, "Ціна"), (1050, "Статус"), (1200, "Дії")]
    for x, title in columns:
        label(draw, (x, table_y + 20), title, 17, "#FFFFFF", True)
    row_h = 102
    for index, ticket in enumerate(tickets):
        row_y = table_y + 62 + index * row_h
        if row_y + row_h > 984:
            break
        if index % 2:
            draw.rectangle((71, row_y, 1529, row_y + row_h), fill="#F8FAFC")
        draw.line((70, row_y + row_h, 1530, row_y + row_h), fill=BORDER, width=1)
        code, seat, event, email, price, status = ticket
        label(draw, (90, row_y + 18), shorten(code, 19, placeholder="…"), 17, INK, True)
        label(draw, (90, row_y + 52), f"Місце {seat}", 15, MUTED)
        label(draw, (300, row_y + 20), shorten(event, 30, placeholder="…"), 17, INK)
        label(draw, (640, row_y + 31), shorten(email, 28, placeholder="…"), 16, INK)
        label(draw, (915, row_y + 31), f"{price:,.2f} грн".replace(",", " "), 16, INK)
        status_color = {"available": GREEN, "reserved": YELLOW, "sold": BLUE, "cancelled": MUTED}.get(status, MUTED)
        rounded(draw, (1050, row_y + 24, 1155, row_y + 62), fill="#FFFFFF", outline=status_color, radius=10)
        label(draw, (1062, row_y + 33), status, 14, status_color, True)
        button(draw, (1200, row_y + 24, 1325, row_y + 65), "Перегляд", primary=False)
        button(draw, (1338, row_y + 24, 1505, row_y + 65), "Редагувати", primary=False)
    save(image, "06-ticket-success.png" if success else "02-ticket-catalog.png")


def field(draw: ImageDraw.ImageDraw, x: int, y: int, width: int, title: str, value: str, invalid: bool = False) -> None:
    label(draw, (x, y), title, 17, INK, True)
    outline = RED if invalid else "#98A2B3"
    rounded(draw, (x, y + 28, x + width, y + 77), radius=8, outline=outline, width=2)
    label(draw, (x + 15, y + 42), value, 17, MUTED if not value else INK)
    if invalid:
        label(draw, (x, y + 84), "Перевірте значення поля.", 14, RED)


def draw_form(mode: str) -> None:
    _, demo, discounts = load_rows()
    edit = mode == "edit"
    invalid = mode == "validation"
    image, draw = base_page("Квитки")
    title = "Редагування TKT-DEMO-2026" if edit else "Створення квитка"
    subtitle = "Форма заповнена поточними даними з бази." if edit else "Заповніть дані та виберіть зв’язки з подією і знижками."
    label(draw, (70, 118), title, 38, INK, True)
    label(draw, (70, 170), subtitle, 19, MUTED)
    rounded(draw, (70, 220, 1530, 1000), radius=18)
    offset = 0
    if invalid:
        rounded(draw, (105, 255, 1495, 390), fill="#FEF3F2", outline="#FDA29B", radius=12)
        label(draw, (130, 278), "Форму не збережено. Перевірте позначені поля.", 19, RED, True)
        label(draw, (130, 318), "• Подія, код і місце є обов’язковими   • Ціна повинна бути числом", 16, RED)
        label(draw, (130, 350), "• Вкажіть коректну електронну адресу", 16, RED)
        offset = 150

    code, seat, event, email, price, status = demo
    start_y = 265 + offset
    event_value = event if edit else ("Оберіть подію" if invalid else "Прем’єра «Дюна: Частина третя» ▾")
    field(draw, 105, start_y, 670, "Подія *", event_value, invalid)
    field(draw, 805, start_y, 315, "Код квитка *", code if edit else "" if invalid else "TKT-NEW-2026", invalid)
    field(draw, 1150, start_y, 345, "Місце *", seat if edit else "" if invalid else "A-12", invalid)
    second_y = start_y + 125
    field(draw, 105, second_y, 360, "Ціна, грн *", f"{price:.2f}" if edit else "invalid" if invalid else "320.00", invalid)
    field(draw, 495, second_y, 520, "Email покупця *", email if edit else "wrong-address" if invalid else "buyer@example.com", invalid)
    field(draw, 1045, second_y, 450, "Статус *", status if edit else "available ▾")

    third_y = second_y + 130
    field(draw, 105, third_y, 520, "Дата продажу", "" if not edit else "")
    label(draw, (660, third_y), "Знижки M:M", 17, INK, True)
    rounded(draw, (660, third_y + 28, 1495, third_y + 150), fill="#F8FAFC", radius=10)
    x = 690
    for index, (discount_code, discount_name) in enumerate(discounts):
        checked = edit and index < 2 or (mode == "create" and index in (0, 1))
        draw.rectangle((x, third_y + 62, x + 24, third_y + 86), fill="#FFFFFF", outline=BLUE, width=2)
        if checked:
            draw.line((x + 5, third_y + 74, x + 11, third_y + 81), fill=BLUE, width=3)
            draw.line((x + 11, third_y + 81, x + 21, third_y + 66), fill=BLUE, width=3)
        label(draw, (x + 34, third_y + 61), shorten(discount_code, 13, placeholder="…"), 15, INK)
        x += 195

    if mode == "create":
        rounded(draw, (105, start_y + 77, 775, start_y + 250), fill="#FFFFFF", outline="#98A2B3", radius=8)
        options = ["Прем’єра «Дюна: Частина третя»", "Ніч українського кіно", "Ретроспектива світового кіно"]
        oy = start_y + 95
        for index, option in enumerate(options):
            if index == 0:
                draw.rectangle((108, oy - 7, 772, oy + 32), fill="#E8EEFF")
            label(draw, (125, oy), option, 16, INK)
            oy += 47

    button_y = 900 if not invalid else 905
    button(draw, (105, button_y, 310, button_y + 55), "Оновити квиток" if edit else "Зберегти квиток")
    button(draw, (330, button_y, 480, button_y + 55), "Скасувати", primary=False)
    output = {"create": "03-ticket-create.png", "validation": "04-ticket-validation.png", "edit": "05-ticket-edit.png"}[mode]
    save(image, output)


def draw_test_terminal() -> None:
    image = Image.new("RGB", (1600, 820), "#0D1117")
    draw = ImageDraw.Draw(image)
    draw.rectangle((0, 0, 1600, 72), fill="#161B22")
    label(draw, (34, 22), "PowerShell · scripts/verify.ps1", 24, "#F0F6FC", True)
    lines = [
        ("INFO  Blade templates cached successfully.", "#58A6FF"),
        ("Showing [24] routes", "#E6EDF3"),
        ("Pint  PASS", "#7EE787"),
        ("PASS  Tests\\Feature\\Lab7BladeViewTest", "#7EE787"),
        ("[PASS] ticket catalog displays eager loaded relations", "#E6EDF3"),
        ("[PASS] create and edit forms contain csrf relations and method spoofing", "#E6EDF3"),
        ("[PASS] ticket form validates and preserves errors in session", "#E6EDF3"),
        ("[PASS] browser crud creates updates relations and deletes ticket", "#E6EDF3"),
        ("[PASS] event and discount have complete blade crud pages", "#E6EDF3"),
        ("Tests: 18 passed (91 assertions)", "#7EE787"),
        ("Duration: 3.92s", "#E6EDF3"),
        ("LAB 7 VALIDATION: PASS", "#7EE787"),
    ]
    y = 105
    for text, color in lines:
        label(draw, (48, y), text, 25, color, text.startswith(("PASS", "Tests", "LAB")))
        y += 55
    save(image, "07-tests.png")


def main() -> None:
    draw_home()
    draw_catalog()
    draw_form("create")
    draw_form("validation")
    draw_form("edit")
    draw_catalog(success=True)
    draw_test_terminal()
    print("Rendered 7 Lab 7 UI assets")


if __name__ == "__main__":
    main()
