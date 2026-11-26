@extends('layouts.app')

@section('title', 'Квитки')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="mb-1">Квитки</h1>
            <p class="text-secondary mb-0">Каталог із подіями та знижками, завантаженими через Eloquent eager loading.</p>
        </div>
        <a href="{{ route('tickets.create') }}" class="btn btn-primary">+ Додати квиток</a>
    </div>

    <form method="GET" action="{{ route('tickets.index') }}" class="card page-card mb-4">
        <div class="card-body row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label" for="event_filter">Подія</label>
                <select class="form-select" name="event_id" id="event_filter">
                    <option value="">Усі події</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" @selected((int) request('event_id') === $event->id)>{{ $event->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="status_filter">Статус</label>
                <select class="form-select" name="status" id="status_filter">
                    <option value="">Усі статуси</option>
                    @foreach(['available' => 'Доступний', 'reserved' => 'Зарезервований', 'sold' => 'Проданий', 'cancelled' => 'Скасований'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-outline-primary flex-grow-1" type="submit">Фільтрувати</button>
                <a class="btn btn-outline-secondary" href="{{ route('tickets.index') }}" aria-label="Скинути фільтри">×</a>
            </div>
        </div>
    </form>

    <div class="card page-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Код / місце</th>
                        <th>Подія</th>
                        <th>Покупець</th>
                        <th>Знижки</th>
                        <th>Ціна</th>
                        <th>Статус</th>
                        <th class="action-cell">Дії</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($tickets as $ticket)
                    @php
                        $status = [
                            'available' => ['Доступний', 'success'],
                            'reserved' => ['Резерв', 'warning'],
                            'sold' => ['Проданий', 'primary'],
                            'cancelled' => ['Скасований', 'secondary'],
                        ][$ticket->status] ?? [$ticket->status, 'secondary'];
                    @endphp
                    <tr>
                        <td><strong>{{ $ticket->code }}</strong><br><span class="text-secondary small">Місце {{ $ticket->seat_number }}</span></td>
                        <td>{{ $ticket->event->name }}<br><span class="text-secondary small">{{ $ticket->event->starts_at->format('d.m.Y H:i') }}</span></td>
                        <td>{{ $ticket->customer_email }}</td>
                        <td>
                            @forelse($ticket->discounts as $discount)
                                <span class="badge text-bg-light border">{{ $discount->code }}</span>
                            @empty
                                <span class="text-secondary">—</span>
                            @endforelse
                        </td>
                        <td class="text-nowrap">{{ number_format($ticket->price, 2, ',', ' ') }} грн</td>
                        <td><span class="badge text-bg-{{ $status[1] }}">{{ $status[0] }}</span></td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-info">Перегляд</a>
                                <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-sm btn-outline-warning">Редагувати</a>
                                <form action="{{ route('tickets.destroy', $ticket) }}" method="POST" onsubmit="return confirm('Видалити квиток {{ $ticket->code }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Видалити</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">Квитків не знайдено. Створіть перший запис.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
            <div class="card-footer bg-white py-3">{{ $tickets->links() }}</div>
        @endif
    </div>
@endsection
