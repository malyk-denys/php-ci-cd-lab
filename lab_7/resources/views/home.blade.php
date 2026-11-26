@extends('layouts.app')

@section('title', 'Головна')

@section('content')
    <section class="hero p-4 p-lg-5 mb-4 shadow-lg">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge text-bg-light text-primary mb-3">Blade · SSR · CRUD</span>
                <h1 class="display-5 fw-bold">Керування продажем квитків</h1>
                <p class="lead mb-4">Єдиний вебінтерфейс для квитків, кіносеансів і знижок із серверною валідацією та захищеними формами.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('tickets.index') }}" class="btn btn-light btn-lg">Відкрити квитки</a>
                    <a href="{{ route('tickets.create') }}" class="btn btn-outline-light btn-lg">Створити квиток</a>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="bg-white bg-opacity-10 rounded-4 p-4">
                    <div class="small text-uppercase opacity-75 mb-2">Предметна область</div>
                    <div class="h4 mb-1">Кінотеатр</div>
                    <p class="mb-0 opacity-75">Event 1:M Ticket<br>Ticket M:M Discount</p>
                </div>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-md-4">
            <article class="stat-card h-100 p-4">
                <div class="text-primary fw-bold mb-2">Головна сутність</div>
                <h2 class="h4">Квитки</h2>
                <p class="text-secondary">Продаж, місце, ціна, покупець, статус, подія та набір знижок.</p>
                <a href="{{ route('tickets.index') }}" class="btn btn-primary">Перейти</a>
            </article>
        </div>
        <div class="col-md-4">
            <article class="stat-card h-100 p-4">
                <div class="text-primary fw-bold mb-2">Зв’язок 1:M</div>
                <h2 class="h4">Події</h2>
                <p class="text-secondary">Кіносеанс містить багато квитків; у формах використано список вибору.</p>
                <a href="{{ route('events.index') }}" class="btn btn-outline-primary">Перейти</a>
            </article>
        </div>
        <div class="col-md-4">
            <article class="stat-card h-100 p-4">
                <div class="text-primary fw-bold mb-2">Зв’язок M:M</div>
                <h2 class="h4">Знижки</h2>
                <p class="text-secondary">До квитка можна застосувати кілька знижок через групу чекбоксів.</p>
                <a href="{{ route('discounts.index') }}" class="btn btn-outline-primary">Перейти</a>
            </article>
        </div>
    </div>
@endsection
