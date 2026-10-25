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


def route(draw, points, color="#71889b", width=5, dashed=False):
    """Draw a multi-segment UML connector, optionally dashed."""
    for start, end in zip(points, points[1:]):
        if dashed:
            import math
            length = math.dist(start, end)
            steps = max(1, int(length // 18))
            for i in range(0, steps, 2):
                a, b = i / steps, min((i + 1) / steps, 1)
                draw.line((start[0] + (end[0] - start[0]) * a,
                           start[1] + (end[1] - start[1]) * a,
                           start[0] + (end[0] - start[0]) * b,
                           start[1] + (end[1] - start[1]) * b), fill=color, width=width)
        else:
            draw.line((*start, *end), fill=color, width=width)


def symbol(draw, point, direction, kind, color=BLUE):
    """UML endpoint marker pointing in the specified direction."""
    import math
    x, y = point
    dx, dy = direction
    length = math.hypot(dx, dy)
    dx, dy = dx / length, dy / length
    px, py = -dy, dx
    if kind == "inheritance":
        pts = [(x, y), (x - 30 * dx + 16 * px, y - 30 * dy + 16 * py),
               (x - 30 * dx - 16 * px, y - 30 * dy - 16 * py)]
        draw.polygon(pts, fill=WHITE, outline=color, width=5)
    elif kind in ("aggregation", "composition"):
        pts = [(x, y), (x + 21 * dx + 13 * px, y + 21 * dy + 13 * py),
               (x + 42 * dx, y + 42 * dy),
               (x + 21 * dx - 13 * px, y + 21 * dy - 13 * py)]
        draw.polygon(pts, fill=color if kind == "composition" else WHITE,
                     outline=color, width=5)


def badge(draw, x, y, message, *, fill=WHITE, color=BLUE):
    face = font(23, True)
    box = draw.textbbox((0, 0), message, font=face)
    width = box[2] - box[0] + 28
    draw.rounded_rectangle((x, y, x + width, y + 42), radius=12, fill=fill, outline=color, width=2)
    draw.text((x + 14, y + 5), message, font=face, fill=color)


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
    # One system boundary and three actor groups.  The connector endpoints
    # are intentionally routed around the oval labels.
    draw.rounded_rectangle((440, 230, 1720, 1995), radius=24, fill="#fbfdff", outline=BLUE, width=5)
    draw.rounded_rectangle((500, 207, 1040, 255), radius=10, fill=BLUE)
    center(draw, (770, 214), "Система продажу квитків", font(27, True), WHITE)
    for top, bottom, label in [(290, 750, "ПЕРЕГЛЯД АФІШІ"),
                                (780, 1435, "КУПІВЛЯ КВИТКІВ"),
                                (1465, 1940, "КЕРУВАННЯ КІНОТЕАТРОМ")]:
        draw.rounded_rectangle((475, top, 1685, bottom), radius=20,
                               fill="#f4f8fc" if top != 780 else "#edf6fc")
        draw.text((515, top + 15), label, font=font(23, True), fill=GRAY)

    groups = [
        (365, "Відвідувач", [
            ("Афіша", 340, 0), ("Розклад", 470, 0), ("Реєстрація / вхід", 600, 0),
            ("Опис фільму", 470, 1)]),
        (900, "Покупець", [
            ("Придбати квиток", 835, 0), ("Промокод", 975, 0),
            ("Мої квитки", 1115, 0), ("Повернути квиток", 1255, 0),
            ("Обрати місця", 835, 1), ("Оплатити", 975, 1),
            ("Електронний квиток", 1115, 1)]),
        (1570, "Адміністратор", [
            ("Керувати фільмами", 1500, 0), ("Керувати сеансами", 1640, 0),
            ("Звіти з продажів", 1780, 0), ("Зали та місця", 1500, 1),
            ("Скасувати сеанс", 1640, 1)]),
    ]
    boxes = {}
    for actor_y, actor_name, cases in groups:
        actor(draw, 230, actor_y, actor_name)
        # Solid actor associations to three or more primary actions.
        primary = [case for case in cases if case[2] == 0]
        route(draw, [(272, actor_y + 67), (405, actor_y + 67),
                     (405, primary[0][1] + 48), (560, primary[0][1] + 48)], width=5)
        for case_name, y, column in primary[1:]:
            route(draw, [(405, y + 48), (560, y + 48)], width=5)
        route(draw, [(405, primary[0][1] + 48), (405, primary[-1][1] + 48)], width=5)
        for case_name, y, column in cases:
            x1 = 560 if column == 0 else 1170
            box = (x1, y, x1 + 430, y + 96)
            oval(draw, box, wrap(draw, case_name, font(30), 360))
            boxes[case_name] = box

    # Associations to secondary actor actions avoid crossing any use case.
    route(draw, [(272, 432), (370, 432), (370, 275), (1120, 275),
                 (1120, 518), (1170, 518)], width=4)
    route(draw, [(272, 1637), (345, 1637), (345, 1445), (1120, 1445),
                 (1120, 1548), (1170, 1548)], width=4)
    # Actor generalization: a registered customer has visitor capabilities.
    route(draw, [(215, 900), (75, 900), (75, 365), (210, 365)], color=NAVY, width=4)
    symbol(draw, (210, 365), (1, 0), "inheritance", NAVY)
    badge(draw, 84, 602, "наслідує", fill=WHITE, color=NAVY)

    # Dashed arrows show the required include and conditional extend links.
    arrow(draw, (990, 867), (1170, 875), BLUE, 5, dashed=True)
    badge(draw, 1013, 810, "«include»")
    arrow(draw, (990, 887), (1170, 1015), BLUE, 5, dashed=True)
    badge(draw, 1040, 934, "«include»")
    arrow(draw, (990, 909), (1170, 1155), BLUE, 5, dashed=True)
    badge(draw, 1062, 1082, "«include»")
    arrow(draw, (770, 975), (770, 931), BLUE, 5, dashed=True)
    badge(draw, 790, 947, "«extend»")
    arrow(draw, (770, 1255), (770, 1211), BLUE, 5, dashed=True)
    badge(draw, 790, 1227, "«extend»")
    arrow(draw, (1170, 1688), (990, 1688), BLUE, 5, dashed=True)
    badge(draw, 1015, 1615, "«extend»")
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
    draw.rounded_rectangle((65, 225, 1735, 1555), radius=24,
                           fill="#f7fbfe", outline="#a9bfd0", width=4)
    draw.rounded_rectangle((65, 1620, 1735, 1860), radius=24,
                           fill="#edf5fb", outline="#a9bfd0", width=4)
    badge(draw, 90, 234, "МОДЕЛІ", fill=BLUE, color=WHITE)
    badge(draw, 90, 1629, "КОНТРОЛЕРИ", fill=BLUE, color=WHITE)

    nodes = {
        "User": (750, 285, 1050, 390),
        "Customer": (285, 520, 585, 625),
        "Administrator": (1215, 520, 1515, 625),
        "Movie": (90, 805, 390, 910),
        "CinemaHall": (750, 805, 1050, 910),
        "Seat": (1410, 805, 1710, 910),
        "Screening": (750, 1070, 1050, 1175),
        "Order": (285, 1370, 585, 1475),
        "Ticket": (750, 1370, 1050, 1475),
        "Payment": (1410, 1370, 1710, 1475),
        "CatalogController": (90, 1690, 450, 1795),
        "OrderController": (720, 1690, 1080, 1795),
        "AdminController": (1350, 1690, 1710, 1795),
    }
    # Inheritance points to the parent class.
    route(draw, [(435, 520), (435, 445), (790, 445), (790, 390)], width=5)
    symbol(draw, (790, 390), (0, -1), "inheritance")
    route(draw, [(1365, 520), (1365, 445), (1010, 445), (1010, 390)], width=5)
    symbol(draw, (1010, 390), (0, -1), "inheritance")
    # Domain associations and whole-part relationships.
    route(draw, [(435, 625), (435, 1370)], width=5)
    badge(draw, 455, 1210, "оформлює")
    route(draw, [(390, 857), (600, 857), (600, 1122), (750, 1122)], width=5)
    symbol(draw, (390, 857), (1, 0), "aggregation")
    badge(draw, 625, 965, "агрегація")
    route(draw, [(1050, 857), (1410, 857)], width=5)
    symbol(draw, (1050, 857), (1, 0), "composition")
    badge(draw, 1105, 790, "композиція")
    route(draw, [(900, 910), (900, 1070)], width=5)
    route(draw, [(900, 1175), (900, 1370)], width=5)
    route(draw, [(585, 1422), (750, 1422)], width=5)
    symbol(draw, (585, 1422), (1, 0), "composition")
    route(draw, [(585, 1465), (670, 1530), (1590, 1530), (1560, 1475)], width=5)
    badge(draw, 1170, 1480, "оплата")
    # Only representative controller dependencies are shown here; all of them
    # remain in class-diagram.puml.
    route(draw, [(270, 1690), (270, 910)], color="#7a99b4", width=4, dashed=True)
    route(draw, [(900, 1690), (900, 1475)], color="#7a99b4", width=4, dashed=True)
    route(draw, [(1530, 1690), (1670, 1600), (1670, 635), (1515, 573)],
          color="#7a99b4", width=4, dashed=True)

    for name, box in nodes.items():
        x1, y1, x2, y2 = box
        fill = WHITE if not name.endswith("Controller") else "#e7f0f8"
        draw.rounded_rectangle(box, radius=15, fill=fill, outline=BLUE, width=5)
        draw.rectangle((x1 + 3, y1 + 3, x1 + 11, y2 - 3), fill=BLUE)
        center(draw, ((x1 + x2) / 2, y1 + 27), name, font(29, True), NAVY)
    draw.rounded_rectangle((90, 1900, 1710, 2120), radius=18,
                           fill="#f5f8fb", outline="#a9bfd0", width=3)
    draw.text((120, 1922), "Позначення", font=font(31, True), fill=NAVY)
    draw.line((130, 1995, 250, 1995), fill=BLUE, width=5)
    draw.text((270, 1978), "асоціація", font=font(25), fill=INK)
    draw.line((550, 1995, 670, 1995), fill=BLUE, width=5)
    symbol(draw, (670, 1995), (1, 0), "inheritance")
    draw.text((695, 1978), "наслідування", font=font(25), fill=INK)
    draw.line((1120, 1995, 1240, 1995), fill=BLUE, width=5)
    symbol(draw, (1120, 1995), (1, 0), "composition")
    draw.text((1270, 1978), "композиція", font=font(25), fill=INK)
    route(draw, [(130, 2060), (250, 2060)], color="#7a99b4", width=4, dashed=True)
    draw.text((270, 2043), "залежність контролера", font=font(25), fill=INK)
    draw.line((830, 2060, 950, 2060), fill=BLUE, width=5)
    symbol(draw, (830, 2060), (1, 0), "aggregation")
    draw.text((980, 2043), "агрегація", font=font(25), fill=INK)
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
