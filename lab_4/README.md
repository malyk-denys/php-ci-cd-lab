# Лабораторна робота №4 — ООП у PHP

Самодостатня реалізація лабораторної роботи з дисципліни «Веб-програмування», варіант 3.

## Реалізовано

- абстрактний клас `AbstractEntity` та моделі `Product`, `User`;
- `ExportableInterface` і `JsonExportTrait`;
- Singleton `Database` та Factory `EntityFactory`;
- `LoggerInterface`, два логери й `EntityService` з Dependency Injection;
- композицію `Book`/`Category`;
- ієрархію `Engine` → `DieselEngine` → `TurbojetEngine`;
- клас `Worker` із формулою `Base + PCount × 0.05 × PP`;
- демонстраційну вебсторінку `index.php`;
- модульні тести й автоматичну перевірку.

## Запуск

```powershell
composer install
php -S 127.0.0.1:8000 index.php
```

Відкрити: <http://127.0.0.1:8000>.

## Перевірка

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\verify.ps1
```

Або окремо:

```powershell
composer lint
composer test
```

Готовий звіт розташований у `docs/Lab4_Report_Variant3.docx`.
