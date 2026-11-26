<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #172033;
            --muted: #617089;
            --line: #dce3ee;
            --accent: #ef3b2d;
            --blue: #285a9f;
            --panel: #ffffff;
            --background: #f3f6fa;
        }

        * { box-sizing: border-box; }
        body { margin: 0; background: var(--background); color: var(--ink); font: 16px/1.55 Inter, Segoe UI, sans-serif; }
        main { max-width: 1120px; margin: 0 auto; padding: 54px 24px 64px; }
        header { display: grid; grid-template-columns: 1.3fr .7fr; gap: 32px; align-items: center; margin-bottom: 34px; }
        .eyebrow { color: var(--blue); font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        h1 { max-width: 760px; margin: 8px 0 16px; font-size: clamp(38px, 6vw, 68px); line-height: 1.04; letter-spacing: -.035em; }
        .lead { max-width: 720px; color: var(--muted); font-size: 19px; }
        .badge { justify-self: end; width: 190px; aspect-ratio: 1; border-radius: 42px; display: grid; place-items: center; background: var(--accent); color: white; box-shadow: 0 20px 45px #ef3b2d36; }
        .badge strong { font-size: 68px; line-height: 1; }
        .badge span { display: block; text-align: center; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
        article { min-height: 200px; padding: 24px; background: var(--panel); border: 1px solid var(--line); border-radius: 18px; box-shadow: 0 10px 30px #1720330a; }
        article h2 { margin: 0 0 10px; font-size: 20px; }
        article p { margin: 0; color: var(--muted); }
        code { display: inline-block; margin-top: 14px; padding: 7px 10px; color: #163c74; background: #eaf2ff; border-radius: 8px; font: 14px/1.4 Consolas, monospace; }
        footer { margin-top: 28px; color: var(--muted); }
        .ok { color: #167647; font-weight: 750; }

        @media (max-width: 760px) {
            header { grid-template-columns: 1fr; }
            .badge { justify-self: start; width: 140px; border-radius: 30px; }
            .badge strong { font-size: 50px; }
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<main>
    <header>
        <div>
            <div class="eyebrow">Веб програмування · Laravel 12 · Варіант 3</div>
            <h1>Система продажу квитків до кінотеатру</h1>
            <p class="lead">Лабораторна робота №5 демонструє декларативні маршрути, сервісні контролери, JSON-відповіді та middleware для перевірки партнерського API-ключа.</p>
        </div>
        <div class="badge" aria-label="Лабораторна робота номер 5">
            <div><strong>5</strong><span>лабораторна</span></div>
        </div>
    </header>

    <section class="grid" aria-label="Компоненти лабораторної роботи">
        <article>
            <h2>Каталог квитків</h2>
            <p>Перелік mock-даних і перегляд квитка за числовим ідентифікатором.</p>
            <code>GET /tickets</code><br>
            <code>GET /tickets/{id}</code>
        </article>
        <article>
            <h2>Створення квитка</h2>
            <p>Валідація події, місця, ціни та електронної адреси клієнта.</p>
            <code>POST /tickets</code>
        </article>
        <article>
            <h2>Middleware</h2>
            <p>Операцію створення захищено заголовком партнерської системи.</p>
            <code>X-Partner-Api-Key</code>
        </article>
    </section>

    <footer><span class="ok">● Система готова</span> · PHP {{ PHP_VERSION }} · {{ app()->version() }}</footer>
</main>
</body>
</html>
