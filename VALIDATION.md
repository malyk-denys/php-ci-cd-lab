# Результати перевірки

Дата перевірки: 15.10.2026.

## Виконано локально

- `scripts/validate.ps1` - **PASS**.
- Перевірено наявність 10 обов'язкових файлів лабораторної.
- `composer.json` успішно розібрано як JSON; перевірено назву пакета, залежності PHPUnit/Pint та PSR-4 mappings.
- Перевірено ключові кроки `php-ci.yml`, зокрема PHP 8.2, Pint, PHPUnit і звернення до `MY_SUPER_SECRET_KEY` через GitHub Secrets.
- Перевірено реалізацію `add`/`subtract` та обидва обов'язкові тестові приклади.
- `git diff --check` не виявив помилок пробілів.
- Створено локальні гілки `main`, `initialize-repository` і `feature-cicd-pipeline` та поетапну історію комітів.

## Перевірено в GitHub Actions

Репозиторій: `https://github.com/malyk-denys/php-ci-cd-lab`.

- Перший запуск `38737889917` очікувано завершився помилкою на кроці Pint через навмисне порушення форматування.
- Після виправлення форматування запуск `38738298167` завершився зі статусом **success**.
- Laravel Pint: **PASS**, перевірено 2 PHP-файли.
- PHPUnit 10.5.66: **OK (2 tests, 2 assertions)**.
- Крок `Demonstrate GitHub Secrets`: **success**; значення `MY_SUPER_SECRET_KEY` у журналі замасковане як `***`.
- Pull Request `#1` відкритий з `feature-cicd-pipeline` у `main`, автор призначений assignee.
- Запрошення collaborator надіслано користувачу `Vitaliiumortols`; прийняття запрошення залежить від нього.

На комп'ютері немає локальних команд `php`, `composer` і `docker`, тому реальні PHP-перевірки виконано на серверному runner GitHub Actions з PHP 8.2.34 і Composer 2.10.3.

Файл `docs/GITHUB_CHECKLIST.md` містить решту зовнішніх кроків. Approvals, branch protection, злиття Pull Request та Kanban не позначаються виконаними без фактичних доказів.

