# Перевірка лабораторної роботи №5

## Локальні перевірки

- таблиця Laravel містить 9 маршрутів;
- `TicketAccessTest`: 7 тестів, 24 твердження;
- повний набір: 8 тестів, 25 тверджень;
- Laravel Pint: без порушень;
- PHP syntax check: без помилок;
- HTTP-сценарії: `200`, `201`, `403`, `404`, `422`.

## Відтворення

```powershell
cd lab_5
composer install
Copy-Item .env.example .env
php artisan key:generate
.\scripts\verify.ps1
```

GitHub Actions виконує ті самі перевірки з робочим каталогом `lab_5`.

