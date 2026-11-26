<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CinemaDesk') · CinemaDesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --cinema-ink: #17233d; --cinema-blue: #2457d6; --cinema-soft: #f4f7fc; }
        body { min-height: 100vh; background: var(--cinema-soft); color: var(--cinema-ink); }
        .navbar-brand { letter-spacing: .02em; font-weight: 800; }
        .app-shell { min-height: calc(100vh - 132px); }
        .page-card { border: 0; border-radius: 1rem; box-shadow: 0 .75rem 2.2rem rgba(23, 35, 61, .08); }
        .table > :not(caption) > * > * { vertical-align: middle; }
        .stat-card { border: 1px solid rgba(36, 87, 214, .12); border-radius: 1rem; background: #fff; }
        .hero { background: linear-gradient(135deg, #17233d 0%, #2457d6 100%); color: #fff; border-radius: 1.25rem; }
        .form-label { font-weight: 600; }
        .required-mark { color: #b42318; }
        .empty-state { padding: 3.5rem 1rem; text-align: center; color: #667085; }
        .badge-soft { color: #344054; background: #eaecf0; }
        .action-cell { min-width: 245px; }
        footer { color: #667085; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">CinemaDesk</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Відкрити навігацію">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Головна</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('tickets.*') ? 'active' : '' }}" href="{{ route('tickets.index') }}">Квитки</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('events.*') ? 'active' : '' }}" href="{{ route('events.index') }}">Події</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('discounts.*') ? 'active' : '' }}" href="{{ route('discounts.index') }}">Знижки</a></li>
            </ul>
            <a class="btn btn-primary" href="{{ route('tickets.create') }}">+ Новий квиток</a>
        </div>
    </div>
</nav>

<main class="app-shell container py-4 py-lg-5">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <strong>Готово.</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрити"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрити"></button>
        </div>
    @endif

    @yield('content')
</main>

<footer class="container border-top py-3 d-flex flex-column flex-md-row justify-content-between gap-2 small">
    <span>Лабораторна робота №7 · Laravel Blade SSR</span>
    <span>Система продажу квитків до кінотеатру</span>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
