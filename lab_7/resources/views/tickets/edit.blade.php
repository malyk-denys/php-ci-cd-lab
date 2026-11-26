@extends('layouts.app')

@section('title', 'Редагування квитка')

@section('content')
    <div class="mb-4">
        <h1 class="mb-1">Редагування {{ $ticket->code }}</h1>
        <p class="text-secondary mb-0">Форма заповнена поточними даними з бази.</p>
    </div>
    <div class="card page-card">
        <div class="card-body p-4">
            <form action="{{ route('tickets.update', $ticket) }}" method="POST" novalidate>
                @csrf
                @method('PUT')
                @include('tickets._form', ['submitLabel' => 'Оновити квиток'])
            </form>
        </div>
    </div>
@endsection
