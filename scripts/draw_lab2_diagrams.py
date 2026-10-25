"""Draw print-sized Lab 2 diagrams with Pillow instead of PlantUML.

The original .puml files remain the complete editable UML specification.  These
pages are laid out specifically for the A4 report so text stays readable.
"""

from __future__ import annotations

import re
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont


ROOT = Path(__file__).resolve().parents[1]
DOCS = ROOT / "docs"
SOURCE = (DOCS / "class-diagram.puml").read_text(encoding="utf-8")
OUT = DOCS / "a4_diagrams"
OUT.mkdir(exist_ok=True)

W, H = 1800, 2200
NAVY = "#173b61"
BLUE = "#205b90"
PALE = "#eaf3fa"
INK = "#17212b"
GRAY = "#617181"
WHITE = "#ffffff"

FONT_PATH = Path("C:/Windows/Fonts/arial.ttf")
BOLD_PATH = Path("C:/Windows/Fonts/arialbd.ttf")


def font(size: int, bold: bool = False):
    return ImageFont.truetype(str(BOLD_PATH if bold else FONT_PATH), size)


def canvas():
    image = Image.new("RGB", (W, H), WHITE)
    return image, ImageDraw.Draw(image)


def center(draw, xy, message, face, fill=INK):
    x, y = xy
    box = draw.textbbox((0, 0), message, font=face)
    draw.text((x - (box[2] - box[0]) / 2, y), message, font=face, fill=fill)


def wrap(draw, message: str, face, width: int):
    lines = []
    for paragraph in message.split("\n"):
        current = ""
        for word in paragraph.split():
            candidate = (current + " " + word).strip()
            if current and draw.textbbox((0, 0), candidate, font=face)[2] > width:
                lines.append(current)
                current = word
            else:
                current = candidate
        lines.append(current)
    return lines


def title(draw, heading: str, subtitle: str):
    center(draw, (W / 2, 46), heading, font(45, True), NAVY)
    center(draw, (W / 2, 108), subtitle, font(27), GRAY)
    draw.line((75, 165, W - 75, 165), fill=BLUE, width=4)


def save(image, name):
    path = OUT / name
    image.save(path, dpi=(300, 300), optimize=True)
    print(path)


def arrow(draw, start, end, color=BLUE, width=5, dashed=False):
    x1, y1 = start
    x2, y2 = end
    if dashed:
        parts = 22
        for i in range(0, parts, 2):
            t1, t2 = i / parts, min((i + 1) / parts, 1)
            draw.line((x1 + (x2 - x1) * t1, y1 + (y2 - y1) * t1,
                       x1 + (x2 - x1) * t2, y1 + (y2 - y1) * t2), fill=color, width=width)
    else:
        draw.line((x1, y1, x2, y2), fill=color, width=width)
    import math
    angle = math.atan2(y2 - y1, x2 - x1)
    length = 20
    spread = 0.46
    points = [(x2, y2),
              (x2 - length * math.cos(angle - spread), y2 - length * math.sin(angle - spread)),
              (x2 - length * math.cos(angle + spread), y2 - length * math.sin(angle + spread))]
    draw.polygon(points, fill=color)


def actor(draw, x, y, label):
    draw.ellipse((x - 20, y, x + 20, y + 40), outline=NAVY, width=5)
    draw.line((x, y + 40, x, y + 105), fill=NAVY, width=5)
    draw.line((x - 42, y + 67, x + 42, y + 67), fill=NAVY, width=5)
    draw.line((x, y + 105, x - 38, y + 148), fill=NAVY, width=5)
    draw.line((x, y + 105, x + 38, y + 148), fill=NAVY, width=5)
    center(draw, (x, y + 160), label, font(29, True), NAVY)


def oval(draw, box, lines):
    draw.ellipse(box, fill=PALE, outline=BLUE, width=4)
    x1, y1, x2, y2 = box
    face = font(30)
    line_height = 33
    first_y = (y1 + y2) / 2 - len(lines) * line_height / 2 - 2
    for i, line in enumerate(lines):
        center(draw, ((x1 + x2) / 2, first_y + i * line_height), line, face)


def use_cases():
    image, draw = canvas()
    title(draw, "Діаграма варіантів використання", "Електронна система продажу квитків до кінотеатру")
    columns = [
        (90, 590, "Відвідувач", ["Переглянути афішу", "Опис фільму", "Переглянути розклад", "Зареєструватися / увійти"]),
        (650, 1150, "Покупець", ["Придбати квиток", "Обрати місця", "Виконати оплату", "Отримати е-квиток", "Застосувати промокод", "Мої квитки", "Повернути квиток"]),
        (1210, 1710, "Адміністратор", ["Керувати фільмами", "Керувати сеансами", "Зали та місця", "Звіти з продажів", "Скасувати сеанс"]),
    ]
    boxes = {}
    for left, right, name, cases in columns:
        draw.rounded_rectangle((left, 220, right, 1600), radius=22, outline="#9ab4c9", width=4)
        x = (left + right) / 2
        actor(draw, x, 245, name)
        top = 520
        trunk_x = left + 32
        draw.line((x - 42, 312, trunk_x, 450, trunk_x, top + (len(cases) - 1) * 145 + 44), fill="#9aafc0", width=4)
        for index, case in enumerate(cases):
            y = top + index * 145
            box = (left + 55, y, right - 55, y + 88)
            draw.line((trunk_x, y + 44, left + 55, y + 44), fill="#9aafc0", width=4)
            oval(draw, box, wrap(draw, case, font(30), 340))
            boxes[case] = box
    # The generalization and include/extend relationships are kept separate
    # from actor associations so the case labels remain legible in print.
    arrow(draw, (900, 213), (340, 213), NAVY, 4)
    center(draw, (620, 185), "узагальнення", font(22), NAVY)
    draw.rounded_rectangle((100, 1650, 1700, 2090), radius=22, fill="#f5f9fc", outline="#9ab4c9", width=4)
    draw.text((135, 1680), "Зв’язки між варіантами використання", font=font(31, True), fill=NAVY)
    relations = [
        ("include", "Придбати квиток → Обрати місця, Виконати оплату, Отримати е-квиток"),
        ("extend", "Застосувати промокод → Придбати квиток"),
        ("extend", "Повернути квиток → Мої квитки"),
        ("extend", "Скасувати сеанс → Керувати сеансами"),
    ]
    for i, (kind, message) in enumerate(relations):
        y = 1745 + i * 75
        draw.rounded_rectangle((135, y, 275, y + 48), radius=12, fill=BLUE)
        center(draw, (205, y + 9), kind, font(23, True), WHITE)
        draw.text((300, y + 8), message, font=font(25), fill=INK)
    save(image, "use-case-a4.png")


def parse_classes():
    result = {}
    for match in re.finditer(r"(?ms)^\s*(?:abstract\s+)?class\s+(\w+)\s*\{(.*?)^\s*\}", SOURCE):
        name, body = match.groups()
        attributes = [s.strip() for s in body.splitlines() if s.strip().startswith("-")]
        operations = [s.strip() for s in body.splitlines() if s.strip().startswith("+")]
        result[name] = (attributes, operations)
    return result


def class_overview():
    image, draw = canvas()
    title(draw, "Діаграма класів", "Огляд зв’язків між 13 класами")
    groups = [
        ("Користувачі", ["User", "Customer", "Administrator"]),
        ("Каталог і кінозал", ["Movie", "CinemaHall", "Seat", "Screening"]),
        ("Продаж квитків", ["Order", "Ticket", "Payment"]),
        ("Контролери", ["CatalogController", "OrderController", "AdminController"]),
    ]
    y = 210
    for heading, names in groups:
        draw.rounded_rectangle((120, y, 1680, y + 350), radius=24, fill="#f5f9fc", outline="#9ab4c9", width=4)
        draw.text((155, y + 22), heading, font=font(31, True), fill=NAVY)
        gap = 20
        card_width = (1480 - gap * (len(names) - 1)) / len(names)
        for i, name in enumerate(names):
            x1 = 160 + i * (card_width + gap)
            x2 = x1 + card_width
            draw.rounded_rectangle((x1, y + 110, x2, y + 245), radius=15, fill=WHITE, outline=BLUE, width=4)
            center(draw, ((x1 + x2) / 2, y + 153), name, font(27, True), NAVY)
        y += 390
    draw.rounded_rectangle((120, 1800, 1680, 2110), radius=20, fill="#eaf3fa", outline=BLUE, width=3)
    draw.text((150, 1830), "Основні типи зв’язків", font=font(31, True), fill=NAVY)
    for i, line in enumerate([
        "Наслідування: Customer та Administrator → User",
        "Агрегація: Movie охоплює Screening",
        "Композиція: CinemaHall містить Seat; Order містить Ticket",
        "Асоціації: Customer–Order; Screening–Ticket; Order–Payment",
        "Залежності: контролери звертаються до відповідних моделей",
    ]):
        draw.text((155, 1890 + i * 40), line, font=font(26), fill=INK)
    save(image, "class-overview-a4.png")


def class_card(draw, name, fields, methods, y):
    left, right = 105, 1695
    f_small = font(31)
    width = 720
    field_lines = [line for item in fields for line in wrap(draw, item, f_small, width)]
    method_lines = [line for item in methods for line in wrap(draw, item, f_small, width)]
    rows = max(len(field_lines), len(method_lines))
    card_h = max(360, 126 + rows * 42)
    draw.rounded_rectangle((left, y, right, y + card_h), radius=18, fill=WHITE, outline=BLUE, width=4)
    draw.rounded_rectangle((left + 2, y + 2, right - 2, y + 65), radius=17, fill=PALE)
    draw.text((135, y + 13), name, font=font(35, True), fill=NAVY)
    draw.line((left, y + 67, right, y + 67), fill=BLUE, width=4)
    draw.line((900, y + 67, 900, y + card_h), fill="#b8cbd9", width=3)
    draw.text((135, y + 82), "Атрибути", font=font(25, True), fill=BLUE)
    draw.text((930, y + 82), "Операції", font=font(25, True), fill=BLUE)
    for i, line in enumerate(field_lines):
        draw.text((135, y + 124 + i * 42), line, font=f_small, fill=INK)
    for i, line in enumerate(method_lines):
        draw.text((930, y + 124 + i * 42), line, font=f_small, fill=INK)
    return y + card_h


def class_details(classes):
    groups = [
        ("Користувачі", ["User", "Customer", "Administrator"], "class-users-a4.png"),
        ("Каталог і кінозал", ["Movie", "CinemaHall", "Seat", "Screening"], "class-catalog-a4.png"),
        ("Замовлення та оплата", ["Order", "Ticket", "Payment"], "class-sales-a4.png"),
        ("Контролери MVC", ["CatalogController", "OrderController", "AdminController"], "class-controllers-a4.png"),
    ]
    for label, names, filename in groups:
        image, draw = canvas()
        title(draw, f"Класи: {label}", "Типізовані атрибути та операції")
        y = 210
        for name in names:
            y = class_card(draw, name, *classes[name], y) + 30
        if y > H - 60:
            raise ValueError(f"Page overflow for {label}: {y}")
        save(image.crop((0, 0, W, min(H, int(y + 35)))), filename)


if __name__ == "__main__":
    classes = parse_classes()
    if len(classes) < 13:
        raise ValueError(f"Expected 13 UML classes, found {len(classes)}")
    use_cases()
    class_overview()
    class_details(classes)
