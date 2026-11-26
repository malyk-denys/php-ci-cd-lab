<?php

declare(strict_types=1);

use App\Http\Controllers\DiscountController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketReportController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/health', static fn () => response()->json([
    'status' => 'OK',
    'timestamp' => now()->toIso8601String(),
]))->name('health');

Route::get('/ticket-reports/summary', [TicketReportController::class, 'summary'])
    ->name('ticket-reports.summary');

Route::resource('events', EventController::class)->only([
    'index', 'store', 'show', 'update', 'destroy',
]);

Route::resource('discounts', DiscountController::class)->only([
    'index', 'store', 'show', 'update', 'destroy',
]);

Route::resource('tickets', TicketController::class)
    ->only(['index', 'store', 'show', 'update', 'destroy'])
    ->middlewareFor(['store', 'update', 'destroy'], 'ticket.partner');
