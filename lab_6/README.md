# Лабораторна робота №6 — Eloquent, міграції та зв'язки

Самодостатній Laravel 12 проєкт для варіанта 3: електронна система продажу квитків до кінотеатру. Проєкт має власні залежності, конфігурацію, SQLite-базу, тести, звіт і сценарії запуску.

## Реалізовано

- моделі `Event`, `Ticket`, `Discount` і міграції для відповідних таблиць;
- зв'язок 1:M: `Event` має багато `Ticket`;
- зв'язок M:M: `Ticket` і `Discount` через `discount_ticket`;
- фабрики та `DatabaseSeeder` для 5 подій, 40 квитків і 10 знижок;
- повний Eloquent CRUD без mock-масивів;
- eager loading для запобігання N+1;
- транзакційний `sync()` знижок квитка;
- каскадне видалення, індекси й унікальні обмеження;
- middleware `VerifyPartnerApiKey`, feature-тести, Pint і окремий GitHub Actions workflow;
- DOCX-звіт, схема даних і матеріали перевірки.

## Встановлення

```powershell
cd lab_6
composer install
Copy-Item .env.example .env
php artisan key:generate
```

## Перевірка

```powershell
.\scripts\verify.ps1
```

Сценарій виконує PHP syntax check, `migrate:fresh --seed`, перегляд статусу міграцій і маршрутів, Laravel Pint та весь PHPUnit-набір. Увага: `migrate:fresh` очищує локальну SQLite-базу лабораторної.

## Запуск

```powershell
.\scripts\start.ps1
```

Застосунок буде доступний за адресою `http://127.0.0.1:8000`. Для захищених запитів до квитків використовується заголовок `X-Partner-Api-Key` зі значенням із `.env`.

Якщо PHP відсутній у `PATH`, задайте шлях через змінну `LAB6_PHP`:

```powershell
$env:LAB6_PHP = 'C:\path\to\php.exe'
```

Технічний опис: [docs/lab6/README.md](docs/lab6/README.md). Звіт: [docs/Lab6_Report_Variant3.docx](docs/Lab6_Report_Variant3.docx).
