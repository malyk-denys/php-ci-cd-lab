@extends('layouts.app')
@section('title', 'Нова знижка')
@section('content')
    <div class="mb-4"><h1 class="mb-1">Створення знижки</h1><p class="text-secondary mb-0">Знижка стане доступною у групі чекбоксів форми квитка.</p></div>
    <div class="card page-card"><div class="card-body p-4"><form action="{{ route('discounts.store') }}" method="POST" novalidate>@csrf @include('discounts._form', ['submitLabel' => 'Зберегти знижку'])</form></div></div>
@endsection
