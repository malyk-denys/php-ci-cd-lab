@extends('layouts.app')

@section('title', 'Квиток ' . $ticket->code)

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div><h1 class="mb-1">{{ $ticket->code }}</h1><p class="text-secondary mb-0">Детальна інформація про квиток.</p></div>
        <div class="d-flex gap-2"><a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-warning">Редагувати</a><a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">До списку</a></div>
    </div>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card page-card h-100"><div class="card-body p-4">
                <h2 class="h5 mb-3">Основні дані</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Подія</dt><dd class="col-sm-8"><a href="{{ route('events.show', $ticket->event) }}">{{ $ticket->event->name }}</a></dd>
                    <dt class="col-sm-4">Місце</dt><dd class="col-sm-8">{{ $ticket->seat_number }}</dd>
                    <dt class="col-sm-4">Покупець</dt><dd class="col-sm-8">{{ $ticket->customer_email }}</dd>
                    <dt class="col-sm-4">Ціна</dt><dd class="col-sm-8">{{ number_format($ticket->price, 2, ',', ' ') }} грн</dd>
                    <dt class="col-sm-4">Статус</dt><dd class="col-sm-8">{{ $ticket->status }}</dd>
                    <dt class="col-sm-4">Продано</dt><dd class="col-sm-8">{{ $ticket->sold_at?->format('d.m.Y H:i') ?? 'Ще не продано' }}</dd>
                </dl>
            </div></div>
        </div>
        <div class="col-lg-5">
            <div class="card page-card h-100"><div class="card-body p-4">
                <h2 class="h5 mb-3">Застосовані знижки</h2>
                @forelse($ticket->discounts as $discount)
                    <a href="{{ route('discounts.show', $discount) }}" class="d-flex justify-content-between text-decoration-none border rounded-3 p-3 mb-2">
                        <span><strong>{{ $discount->code }}</strong><br><small class="text-secondary">{{ $discount->name }}</small></span>
                        <span>{{ $discount->type === 'percentage' ? $discount->value . '%' : number_format($discount->value, 2, ',', ' ') . ' грн' }}</span>
                    </a>
                @empty
                    <p class="text-secondary mb-0">До квитка не прив’язано знижок.</p>
                @endforelse
            </div></div>
        </div>
    </div>
@endsection
