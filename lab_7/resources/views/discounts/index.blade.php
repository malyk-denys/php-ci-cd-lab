@extends('layouts.app')
@section('title', 'Знижки')
@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div><h1 class="mb-1">Знижки</h1><p class="text-secondary mb-0">Довідник промокодів для зв’язку M:M із квитками.</p></div>
        <a href="{{ route('discounts.create') }}" class="btn btn-primary">+ Додати знижку</a>
    </div>
    <div class="card page-card overflow-hidden"><div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-dark"><tr><th>Код</th><th>Назва</th><th>Тип</th><th>Значення</th><th>Період</th><th>Стан</th><th>Квитків</th><th class="action-cell">Дії</th></tr></thead>
        <tbody>
        @forelse($discounts as $discount)
            <tr>
                <td><strong>{{ $discount->code }}</strong></td><td>{{ $discount->name }}</td><td>{{ $discount->type === 'percentage' ? 'Відсоткова' : 'Фіксована' }}</td>
                <td>{{ $discount->type === 'percentage' ? rtrim(rtrim(number_format($discount->value, 2, '.', ''), '0'), '.') . '%' : number_format($discount->value, 2, ',', ' ') . ' грн' }}</td>
                <td class="small">{{ $discount->active_from?->format('d.m.Y') ?? '—' }} — {{ $discount->active_until?->format('d.m.Y') ?? 'безстроково' }}</td>
                <td><span class="badge text-bg-{{ $discount->is_active ? 'success' : 'secondary' }}">{{ $discount->is_active ? 'Активна' : 'Вимкнена' }}</span></td><td>{{ $discount->tickets_count }}</td>
                <td><div class="d-flex flex-wrap gap-1"><a href="{{ route('discounts.show', $discount) }}" class="btn btn-sm btn-outline-info">Перегляд</a><a href="{{ route('discounts.edit', $discount) }}" class="btn btn-sm btn-outline-warning">Редагувати</a><form action="{{ route('discounts.destroy', $discount) }}" method="POST" onsubmit="return confirm('Видалити знижку {{ $discount->code }}?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Видалити</button></form></div></td>
            </tr>
        @empty<tr><td colspan="8" class="empty-state">Знижок ще немає.</td></tr>@endforelse
        </tbody>
    </table></div>@if($discounts->hasPages())<div class="card-footer bg-white py-3">{{ $discounts->links() }}</div>@endif</div>
@endsection
