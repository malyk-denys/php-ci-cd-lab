@extends('layouts.app')

@section('title', 'Новий квиток')

@section('content')
    <div class="mb-4">
        <h1 class="mb-1">Створення квитка</h1>
        <p class="text-secondary mb-0">Заповніть дані та виберіть зв’язки з подією і знижками.</p>
    </div>
    <div class="card page-card">
        <div class="card-body p-4">
            <form action="{{ route('tickets.store') }}" method="POST" novalidate>
                @csrf
                @include('tickets._form', ['submitLabel' => 'Зберегти квиток'])
            </form>
        </div>
    </div>
@endsection
