# Лабораторна робота №7

## Індивідуальний варіант

- Варіант: 3.
- Предметна область: електронна система продажу квитків до кінотеатру.
- Головна сутність: `Ticket`.
- Зв’язок 1:M: `Event` має багато `Ticket`; у формі квитка подія вибирається через `<select>`.
- Зв’язок M:M: `Ticket` має багато `Discount`; у формі квитка знижки вибираються чекбоксами.

## Реалізований вебінтерфейс

| Сутність | Список | Створення | Перегляд | Редагування | Видалення |
|---|---|---|---|---|---|
| Ticket | `tickets.index` | `tickets.create` | `tickets.show` | `tickets.edit` | DELETE `/tickets/{ticket}` |
| Event | `events.index` | `events.create` | `events.show` | `events.edit` | DELETE `/events/{event}` |
| Discount | `discounts.index` | `discounts.create` | `discounts.show` | `discounts.edit` | DELETE `/discounts/{discount}` |

Усі сторінки успадковують `resources/views/layouts/app.blade.php`. Повторювані поля реалізовано анонімними Blade-компонентами в `resources/views/components/form`. У формах зміни даних присутня директива `@csrf`, у формах оновлення та видалення - `@method('PUT')` і `@method('DELETE')`.

`TicketController` використовує `with()` і `load()` для зв’язків, `Rule::unique()->ignore()` для оновлення коду й місця та `sync()` для проміжної таблиці `discount_ticket`. Контролери зберігають сумісність із JSON API попередніх лабораторних: API-відповідь обирається для запитів з `Accept: application/json`, а звичайний браузер отримує Blade HTML і перенаправлення з flash-повідомленням.

## Узгодження з прецедентами

| Прецедент | Сутності та зв’язки | Blade-сторінки | Маршрути |
|---|---|---|---|
| Переглянути каталог квитків | Ticket, Event, Discount | `tickets/index.blade.php`, `tickets/show.blade.php` | GET `/tickets`, GET `/tickets/{ticket}` |
| Продати або зарезервувати квиток | Ticket → Event, Ticket ↔ Discount | `tickets/create.blade.php`, `tickets/_form.blade.php` | GET `/tickets/create`, POST `/tickets` |
| Змінити дані квитка | Ticket → Event, Ticket ↔ Discount | `tickets/edit.blade.php`, `tickets/_form.blade.php` | GET `/tickets/{ticket}/edit`, PUT `/tickets/{ticket}` |
| Скасувати запис квитка | Ticket | форма DELETE у каталозі | DELETE `/tickets/{ticket}` |
| Керувати кіносеансами | Event 1:M Ticket | `events/*.blade.php` | ресурс `/events` |
| Керувати промокодами | Discount M:M Ticket | `discounts/*.blade.php` | ресурс `/discounts` |

## Запуск і перевірка

```powershell
.\scripts\verify.ps1
.\scripts\start.ps1
```

`verify.ps1` перебудовує та наповнює SQLite, компілює всі Blade-шаблони, перевіряє 24 вебмаршрути, запускає Pint і повний PHPUnit-набір. Спеціальні сценарії лабораторної містяться у `tests/Feature/Lab7BladeViewTest.php`.

## Звіт

- `Lab7_Report_Variant3.docx` - редагована версія;
- `Lab7_Report_Variant3.pdf` - готова версія для перегляду та здачі;
- відповіді на контрольні питання до звіту не включено.
