# Лабораторна робота №5 — Laravel маршрутизація, контролери та middleware

Самодостатній Laravel 12 проєкт для варіанта 3: електронна система продажу квитків до кінотеатру. Лабораторна продовжує предметну область ЛР №2, але має власні залежності, конфігурацію, тести, звіт і скрипти запуску.

## Реалізовано

- `GET /tickets` — перелік квитків;
- `POST /tickets` — створення квитка з валідацією;
- `GET /tickets/{id}` — перегляд квитка або відповідь `404`;
- middleware `VerifyPartnerApiKey` для заголовка `X-Partner-Api-Key`;
- службові маршрути `/health` і `/ticket-reports/summary`;
- JSON-відповіді зі статусами `200`, `201`, `403`, `404` і `422`;
- PHPUnit feature-тести та Laravel Pint;
- окремий GitHub Actions workflow.

## Встановлення

```powershell
cd lab_5
composer install
Copy-Item .env.example .env
php artisan key:generate
```

## Запуск

```powershell
.\scripts\start.ps1
```

Застосунок буде доступний за адресою `http://127.0.0.1:8000`.

## Перевірка

```powershell
.\scripts\verify.ps1
```

Скрипт перевіряє синтаксис PHP-файлів, таблицю маршрутів, форматування Pint і весь PHPUnit-набір. Деталі реалізації наведено в [технічній документації](docs/lab5/README.md), а результати — у [VALIDATION.md](VALIDATION.md).

Якщо PHP не доступний через `PATH`, шлях до нього можна передати змінною середовища `LAB5_PHP`.
