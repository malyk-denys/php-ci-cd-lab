<?php

declare(strict_types=1);

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

Route::prefix('tickets')->name('tickets.')->group(function (): void {
    Route::get('/', [TicketController::class, 'index'])->name('index');
    Route::post('/', [TicketController::class, 'store'])
        ->middleware('ticket.partner')
        ->name('store');
    Route::get('/{id}', [TicketController::class, 'show'])
        ->whereNumber('id')
        ->name('show');
});
