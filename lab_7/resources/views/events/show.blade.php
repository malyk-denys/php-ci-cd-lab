@extends('layouts.app')
@section('title', $event->name)
@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div><h1 class="mb-1">{{ $event->name }}</h1><p class="text-secondary mb-0">{{ $event->venue }} · {{ $event->starts_at->format('d.m.Y H:i') }}</p></div>
        <div class="d-flex gap-2"><a href="{{ route('events.edit', $event) }}" class="btn btn-warning">Редагувати</a><a href="{{ route('events.index') }}" class="btn btn-outline-secondary">До списку</a></div>
    </div>
    <div class="card page-card mb-4"><div class="card-body p-4"><h2 class="h5">Опис</h2><p class="mb-0">{{ $event->description ?: 'Опис не вказано.' }}</p></div></div>
    <div class="card page-card overflow-hidden">
        <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center"><h2 class="h5 mb-0">Квитки події</h2><a href="{{ route('tickets.create', ['event_id' => $event->id]) }}" class="btn btn-sm btn-primary">+ Квиток</a></div>
        <div class="table-responsive"><table class="table mb-0"><thead class="table-light"><tr><th>Код</th><th>Місце</th><th>Покупець</th><th>Ціна</th><th>Знижки</th></tr></thead><tbody>
            @forelse($event->tickets as $ticket)
                <tr><td><a href="{{ route('tickets.show', $ticket) }}">{{ $ticket->code }}</a></td><td>{{ $ticket->seat_number }}</td><td>{{ $ticket->customer_email }}</td><td>{{ number_format($ticket->price, 2, ',', ' ') }} грн</td><td>{{ $ticket->discounts->pluck('code')->join(', ') ?: '—' }}</td></tr>
            @empty<tr><td colspan="5" class="empty-state">Для події ще немає квитків.</td></tr>@endforelse
        </tbody></table></div>
    </div>
@endsection
