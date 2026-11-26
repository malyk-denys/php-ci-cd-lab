# PHP CI/CD Lab

## Лабораторна робота №7 — варіант 3

Самодостатній Laravel-проєкт із Blade layout, компонентами, формами та повним CRUD для `Ticket`, `Event` і `Discount` розміщено в [`lab_7/`](lab_7/README.md). Він містить зв'язки 1:M і M:M у формах, серверну валідацію, CSRF, Method Spoofing, тести, CI та DOCX-звіт без відповідей на контрольні питання.

[![Lab 7 CI](https://github.com/malyk-denys/php-ci-cd-lab/actions/workflows/lab7-ci.yml/badge.svg)](https://github.com/malyk-denys/php-ci-cd-lab/actions/workflows/lab7-ci.yml)

## Лабораторна робота №6 — варіант 3

Самодостатній Laravel-проєкт з Eloquent-моделями, міграціями та зв'язками 1:M і M:M розміщено в [`lab_6/`](lab_6/README.md). Він містить SQLite-схему, фабрики, сідер, CRUD, тести, CI та DOCX-звіт.

[![Lab 6 CI](https://github.com/malyk-denys/php-ci-cd-lab/actions/workflows/lab6-ci.yml/badge.svg)](https://github.com/malyk-denys/php-ci-cd-lab/actions/workflows/lab6-ci.yml)

## Лабораторна робота №5 — варіант 3

Самодостатній Laravel-проєкт системи продажу квитків розміщено в [`lab_5/`](lab_5/README.md). Він містить власні залежності, маршрути, контролери, middleware, тести, скрипти запуску, CI та DOCX-звіт.

[![Lab 5 CI](https://github.com/malyk-denys/php-ci-cd-lab/actions/workflows/lab5-ci.yml/badge.svg)](https://github.com/malyk-denys/php-ci-cd-lab/actions/workflows/lab5-ci.yml)

## Лабораторна робота №3 — варіант 3

Реалізація основ PHP та задач 2 / 3 / 7: [інструкція, алгоритми та пояснення](docs/lab3/README.md).
Запуск: `.\scripts\start-lab3.ps1`, перевірка: `.\scripts\verify-lab3.ps1`.

[![PHP CI Application](https://github.com/malyk-denys/php-ci-cd-lab/actions/workflows/php-ci.yml/badge.svg)](https://github.com/malyk-denys/php-ci-cd-lab/actions/workflows/php-ci.yml)

Навчальний PHP-проєкт для лабораторної роботи №1 з керування версіями та налаштування CI/CD у GitHub Actions.

## Функціональність

- додавання двох цілих чисел;
- віднімання двох цілих чисел;
- множення двох цілих чисел;
- модульні тести PHPUnit;
- автоматична перевірка стилю Laravel Pint;
- CI-пайплайн для `push` і `pull_request` у гілку `main`;
- демонстрація безпечного використання GitHub Secret.

## Структура

```text
lab_1/
├── .github/
│   ├── workflows/php-ci.yml
│   └── PULL_REQUEST_TEMPLATE.md
├── app/Services/Calculator.php
├── docs/
├── scripts/validate.ps1
├── tests/Unit/CalculatorTest.php
├── .gitignore
├── composer.json
└── phpunit.xml
```

## Запуск перевірок

PHP і Composer локально не потрібні для виконання лабораторної: команди автоматично виконує GitHub Actions. Якщо вони встановлені, перевірки можна запустити так:

```bash
composer install
composer lint
composer test
```

Структурну перевірку у Windows можна виконати без PHP:

```powershell
.\scripts\validate.ps1
```

## Як працює CI

- `on` визначає події запуску: push та Pull Request до `main`.
- `jobs` містить роботу `build`, яка виконується на `ubuntu-latest`.
- `steps` послідовно завантажує код, налаштовує PHP 8.2, встановлює залежності, запускає Pint і PHPUnit, а потім демонструє маскування secret.

Для успішного виконання останнього кроку в налаштуваннях репозиторію має існувати secret `MY_SUPER_SECRET_KEY`. Його значення не слід додавати у файли проєкту.

Детальні GitHub-кроки та перелік доказів для звіту наведено у [docs/GITHUB_CHECKLIST.md](docs/GITHUB_CHECKLIST.md).

