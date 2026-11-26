# Лабораторна робота №6

## Індивідуальний варіант

- Варіант: 3.
- Предметна область: електронна система продажу квитків до кінотеатру.
- Головна сутність: `Ticket`.
- Зв'язок 1:M: `Event` має багато `Ticket`, квиток належить одній події.
- Зв'язок M:M: `Ticket` має багато `Discount`, знижка застосовується до багатьох квитків.
- Проміжна таблиця: `discount_ticket`.

## Реалізована схема

| Сутність / зв'язок | Міграція | Модель і методи | Маршрути |
|---|---|---|---|
| Event | `create_events_table` | `Event::tickets()` / `hasMany` | `/events` |
| Ticket | `create_tickets_table` | `Ticket::event()` / `belongsTo`; `Ticket::discounts()` / `belongsToMany` | `/tickets` |
| Discount | `create_discounts_table` | `Discount::tickets()` / `belongsToMany` | `/discounts` |
| Ticket ↔ Discount | `create_discount_ticket_table` | `belongsToMany(...)->withTimestamps()` | синхронізація через поле `discounts` у POST/PUT `/tickets` |

Міграції містять первинні та зовнішні ключі, каскадне видалення, унікальні обмеження для коду квитка, коду знижки, slug події та пари «подія + місце», індекси для частих вибірок і складений унікальний індекс у pivot-таблиці.

## Active Record і REST API

`TicketController` більше не використовує mock-масив. Усі операції виконуються через Eloquent. Для списку й окремого квитка зв'язки підвантажуються через `with()`/`load()`, а M:M оновлюється методом `sync()` у транзакції.

Доступні ресурсні методи `index`, `store`, `show`, `update`, `destroy` для `/events`, `/tickets`, `/discounts`. Операції зміни квитків захищені middleware `ticket.partner` і заголовком `X-Partner-Api-Key`.

Приклад створення квитка:

```powershell
curl.exe -i -X POST http://127.0.0.1:8000/tickets `
  -H "Accept: application/json" `
  -H "Content-Type: application/json" `
  -H "X-Partner-Api-Key: lab5-partner-key" `
  -d '{"event_id":1,"code":"TKT-DEMO-001","seat_number":"D-15","price":240,"customer_email":"denys@example.com","status":"reserved","discounts":[1,2]}'
```

## Запуск і перевірка

```powershell
.\scripts\verify.ps1
.\scripts\start.ps1
```

`verify.ps1` навмисно виконує `migrate:fresh --seed`: локальна SQLite-база очищується, після чого створюються 10 знижок, 5 подій і 40 квитків із M:M-зв'язками. Далі сценарій перевіряє статус міграцій, маршрути, Pint і повний PHPUnit-набір.

Основні інтеграційні перевірки розміщені у `tests/Feature/TicketDatabaseTest.php` та використовують `RefreshDatabase`.
