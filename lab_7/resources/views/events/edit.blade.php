@extends('layouts.app')
@section('title', 'Редагування події')
@section('content')
    <div class="mb-4"><h1 class="mb-1">Редагування події</h1><p class="text-secondary mb-0">{{ $event->name }}</p></div>
    <div class="card page-card"><div class="card-body p-4"><form action="{{ route('events.update', $event) }}" method="POST" novalidate>@csrf @method('PUT') @include('events._form', ['submitLabel' => 'Оновити подію'])</form></div></div>
@endsection
