# PHP CI/CD Lab

[![PHP CI Application](https://github.com/malyk-denys/php-ci-cd-lab/actions/workflows/php-ci.yml/badge.svg)](https://github.com/malyk-denys/php-ci-cd-lab/actions/workflows/php-ci.yml)

Навчальний PHP-проєкт для лабораторної роботи №1 з керування версіями та налаштування CI/CD у GitHub Actions.

## Функціональність

- додавання двох цілих чисел;
- віднімання двох цілих чисел;
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

