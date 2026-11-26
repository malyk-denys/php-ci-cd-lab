@extends('layouts.app')
@section('title', 'Нова подія')
@section('content')
    <div class="mb-4"><h1 class="mb-1">Створення події</h1><p class="text-secondary mb-0">Подія стане доступною у списку 1:M форми квитка.</p></div>
    <div class="card page-card"><div class="card-body p-4"><form action="{{ route('events.store') }}" method="POST" novalidate>@csrf @include('events._form', ['submitLabel' => 'Зберегти подію'])</form></div></div>
@endsection
