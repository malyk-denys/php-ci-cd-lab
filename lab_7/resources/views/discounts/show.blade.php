@extends('layouts.app')
@section('title', $discount->code)
@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4"><div><h1 class="mb-1">{{ $discount->code }}</h1><p class="text-secondary mb-0">{{ $discount->name }}</p></div><div class="d-flex gap-2"><a href="{{ route('discounts.edit', $discount) }}" class="btn btn-warning">Редагувати</a><a href="{{ route('discounts.index') }}" class="btn btn-outline-secondary">До списку</a></div></div>
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="stat-card p-3 h-100"><div class="small text-secondary">Тип</div><strong>{{ $discount->type === 'percentage' ? 'Відсоткова' : 'Фіксована' }}</strong></div></div>
        <div class="col-md-3"><div class="stat-card p-3 h-100"><div class="small text-secondary">Значення</div><strong>{{ $discount->type === 'percentage' ? $discount->value . '%' : number_format($discount->value, 2, ',', ' ') . ' грн' }}</strong></div></div>
        <div class="col-md-3"><div class="stat-card p-3 h-100"><div class="small text-secondary">Період</div><strong>{{ $discount->active_from?->format('d.m.Y') ?? '—' }} — {{ $discount->active_until?->format('d.m.Y') ?? '∞' }}</strong></div></div>
        <div class="col-md-3"><div class="stat-card p-3 h-100"><div class="small text-secondary">Стан</div><strong>{{ $discount->is_active ? 'Активна' : 'Вимкнена' }}</strong></div></div>
    </div>
    <div class="card page-card overflow-hidden"><div class="card-header bg-white p-3"><h2 class="h5 mb-0">Пов’язані квитки</h2></div><div class="table-responsive"><table class="table mb-0"><thead class="table-light"><tr><th>Квиток</th><th>Подія</th><th>Покупець</th><th>Ціна</th></tr></thead><tbody>
        @forelse($discount->tickets as $ticket)<tr><td><a href="{{ route('tickets.show', $ticket) }}">{{ $ticket->code }}</a></td><td>{{ $ticket->event->name }}</td><td>{{ $ticket->customer_email }}</td><td>{{ number_format($ticket->price, 2, ',', ' ') }} грн</td></tr>@empty<tr><td colspan="4" class="empty-state">Знижку ще не застосовано до квитків.</td></tr>@endforelse
    </tbody></table></div></div>
@endsection
