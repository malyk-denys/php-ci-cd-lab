@extends('layouts.app')

@section('title', 'Події')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div><h1 class="mb-1">Події</h1><p class="text-secondary mb-0">Кіносеанси та кількість пов’язаних квитків.</p></div>
        <a href="{{ route('events.create') }}" class="btn btn-primary">+ Додати подію</a>
    </div>
    <div class="card page-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark"><tr><th>Подія</th><th>Місце</th><th>Початок</th><th>Статус</th><th>Квитків</th><th class="action-cell">Дії</th></tr></thead>
                <tbody>
                @forelse($events as $event)
                    <tr>
                        <td><strong>{{ $event->name }}</strong><br><code>{{ $event->slug }}</code></td>
                        <td>{{ $event->venue }}</td>
                        <td class="text-nowrap">{{ $event->starts_at->format('d.m.Y H:i') }}</td>
                        <td><span class="badge badge-soft">{{ $event->status }}</span></td>
                        <td>{{ $event->tickets_count }}</td>
                        <td><div class="d-flex flex-wrap gap-1">
                            <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-outline-info">Перегляд</a>
                            <a href="{{ route('events.edit', $event) }}" class="btn btn-sm btn-outline-warning">Редагувати</a>
                            <form action="{{ route('events.destroy', $event) }}" method="POST" onsubmit="return confirm('Видалити подію та всі її квитки?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Видалити</button></form>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">Подій ще немає.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($events->hasPages())<div class="card-footer bg-white py-3">{{ $events->links() }}</div>@endif
    </div>
@endsection
