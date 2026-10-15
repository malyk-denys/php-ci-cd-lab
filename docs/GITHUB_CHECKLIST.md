# Чекліст виконання GitHub-частини

Локальна реалізація готова. Наведені нижче дії створюють зовнішні об'єкти GitHub і мають виконуватися у репозиторії, де власник може додати двох реальних одногрупників.

## 1. Репозиторій і початкова гілка

- [x] Створити публічний репозиторій `php-ci-cd-lab` на GitHub.
- [x] Опублікувати початковий `README.md` у `main`.
- [x] Створити гілку `initialize-repository` з `.gitignore` і `.github/PULL_REQUEST_TEMPLATE.md`.
- [ ] Відкрити Pull Request `initialize-repository -> main`.
- [ ] Додати двох одногрупників як collaborators і reviewers, а себе - як assignee.
- [ ] Отримати два реальні approve та злити Pull Request.

## 2. Безпека і захист `main`

- [x] У `Settings -> Secrets and variables -> Actions` створити secret `MY_SUPER_SECRET_KEY` зі значенням не коротшим за 8 символів.
- [ ] У ruleset або branch protection для `main` увімкнути `Require status checks to pass before merging`.
- [ ] Вибрати обов'язкову перевірку `build` із workflow `PHP CI Application`.

## 3. CI-пайплайн

- [x] Створити гілку `feature-cicd-pipeline`.
- [x] Для першого CI-запуску тимчасово використати невідформатований метод із `docs/INITIAL_FAILURE.md`.
- [ ] Відкрити Pull Request у `main` і зробити скріншоти червоного статусу, логу Pint та заблокованого Merge.
- [x] Замінити файл на фінальну відформатовану версію `app/Services/Calculator.php` і відправити новий commit.
- [x] Переконатися, що Pint і PHPUnit успішні, а в кроці `Demonstrate GitHub Secrets` значення показано як `***`.
- [ ] Зробити скріншоти зеленого workflow, замаскованого secret і README з badge.
- [ ] Злити Pull Request у `main`.

## 4. Issues і Kanban

Створити Project типу Board і три Issues:

1. `Додати множення до Calculator` - label `enhancement`.
2. `Розширити тести граничними значеннями` - label `enhancement`.
3. `Доповнити документацію прикладами` - label `documentation`.

- [ ] Призначити кожну задачу на себе та додати до Project.
- [ ] Перемістити одну задачу з `Todo` до `In Progress`.
- [ ] Створити гілку `feature/issue-N`, де `N` - номер вибраної задачі.
- [ ] Виконати логічну зміну та зробити commit із `Fixes #N`.
- [ ] У описі Pull Request написати `Resolves #N`.
- [ ] Після merge перевірити автоматичне закриття Issue та перехід картки у `Done`.

## 5. Обов'язкові скріншоти для звіту

1. Невдалий CI та лог помилки Pint.
2. Крок із замаскованим secret (`***`).
3. Заблокована кнопка Merge через захист `main`.
4. README із зеленим status badge.
5. Kanban-дошка із задачами в `Todo`, `In Progress`, `Done`.
6. Автоматично закрита Issue з посиланням на commit або Pull Request.

