# Лабораторна робота №7 — Blade, компоненти та форми

Самодостатній Laravel 12 проєкт для варіанта 3: електронна система продажу квитків до кінотеатру. Проєкт продовжує предметну модель ЛР 6 і додає повний серверний вебінтерфейс для `Ticket`, `Event` та `Discount`.

## Реалізовано

- спільний Blade layout із навігацією та flash-повідомленнями;
- повторно використовувані компоненти полів, textarea і помилок;
- повний CRUD для квитків, подій і знижок;
- вибір події для зв'язку `Event` 1:M `Ticket`;
- вибір кількох знижок для зв'язку `Ticket` M:M `Discount`;
- `@csrf`, `@method`, `old()`, `@error` і серверна валідація;
- eager loading, пагінація та транзакційний `sync()`;
- збереження JSON API попередніх лабораторних;
- feature-тести Blade UI та окремий GitHub Actions workflow;
- DOCX-звіт без відповідей на контрольні питання.

## Встановлення

```powershell
cd lab_7
composer install
Copy-Item .env.example .env
php artisan key:generate
```

## Перевірка

```powershell
.\scripts\verify.ps1
```

Сценарій генерує ключ застосунку, перебудовує та наповнює SQLite-базу, компілює Blade, перевіряє 24 маршрути, запускає Pint і повний PHPUnit-набір. Увага: `migrate:fresh` очищує локальну SQLite-базу лабораторної.

## Запуск

```powershell
.\scripts\start.ps1
```

Застосунок буде доступний за адресою `http://127.0.0.1:8000`. Якщо PHP відсутній у `PATH`, задайте шлях через змінну `LAB7_PHP`.

Технічний опис: [docs/lab7/README.md](docs/lab7/README.md). Звіт: [docs/lab7/Lab7_Report_Variant3.docx](docs/lab7/Lab7_Report_Variant3.docx).
