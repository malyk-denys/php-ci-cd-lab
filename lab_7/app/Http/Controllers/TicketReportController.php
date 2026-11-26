<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;

final class TicketReportController extends Controller
{
    public function summary(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'domain' => 'cinema_ticket_sales',
                'events' => Event::count(),
                'tickets' => Ticket::count(),
                'available_tickets' => Ticket::where('status', 'available')->count(),
                'active_discounts' => Discount::where('is_active', true)->count(),
                'currency' => 'UAH',
                'storage' => 'eloquent_sqlite',
            ],
        ]);
    }
}
