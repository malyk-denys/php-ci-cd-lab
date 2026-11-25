# Лабораторна робота №5

Робота оформлена як окремий самодостатній Laravel-проєкт у каталозі `lab_5/`. Усі наведені нижче шляхи відраховуються від цього каталогу.

## Індивідуальний варіант

- Варіант: 3.
- Предметна область: електронна система продажу квитків до кінотеатру.
- Головна сутність: `Ticket`.
- Ресурсний контролер: `TicketController`.
- Префікс маршрутів: `/tickets`.
- Обов'язкові атрибути: `event_name`, `seat_number`, `price`, `customer_email`.
- Бізнес-правило middleware: POST-запит дозволений лише партнеру з правильним `X-Partner-Api-Key`.

## Узгодження з лабораторною роботою №2

| Артефакт ЛР №2 | Реалізація ЛР №5 |
|---|---|
| Сутність Ticket | `app/Http/Controllers/TicketController.php` |
| Перегляд каталогу квитків | `GET /tickets`, метод `index()` |
| Перегляд конкретного квитка | `GET /tickets/{id}`, метод `show()` |
| Бронювання або створення квитка | `POST /tickets`, метод `store()` |
| Атрибути події, місця, ціни та клієнта | правила валідації у `store()` |
| Доступ партнерської системи | `VerifyPartnerApiKey` і заголовок `X-Partner-Api-Key` |

## Реалізовані компоненти

- Laravel 12.71 із PHP 8.2;
- `TicketController` із mock-сховищем, JSON-відповідями 200, 201 і 404;
- `TicketReportController` для службового підсумку;
- іменовані маршрути та числове обмеження `whereNumber('id')`;
- middleware `VerifyPartnerApiKey`, зареєстрований як `ticket.partner`;
- тимчасовий CSRF-виняток лише для `/tickets`;
- 7 feature-тестів позитивних і негативних сценаріїв;
- GitHub Actions із Composer, APP_KEY, Pint і `php artisan test`.

## Приклади запитів

```powershell
curl.exe -i http://127.0.0.1:8000/tickets

curl.exe -i http://127.0.0.1:8000/tickets/999

curl.exe -i -X POST http://127.0.0.1:8000/tickets `
  -H "Accept: application/json" `
  -H "Content-Type: application/json" `
  -H "X-Partner-Api-Key: lab5-partner-key" `
  -d '{"event_name":"Interstellar","seat_number":"D-15","price":240,"customer_email":"denys@example.com"}'
```

Без заголовка `X-Partner-Api-Key` той самий POST-запит повертає `403 Forbidden`.

## Запуск і перевірка

```powershell
.\scripts\start.ps1
.\scripts\verify.ps1
```
