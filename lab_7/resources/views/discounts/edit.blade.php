@extends('layouts.app')
@section('title', 'Редагування знижки')
@section('content')
    <div class="mb-4"><h1 class="mb-1">Редагування знижки</h1><p class="text-secondary mb-0">{{ $discount->code }} · {{ $discount->name }}</p></div>
    <div class="card page-card"><div class="card-body p-4"><form action="{{ route('discounts.update', $discount) }}" method="POST" novalidate>@csrf @method('PUT') @include('discounts._form', ['submitLabel' => 'Оновити знижку'])</form></div></div>
@endsection
