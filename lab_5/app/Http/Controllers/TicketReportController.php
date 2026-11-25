<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

final class TicketReportController extends Controller
{
    public function summary(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'domain' => 'cinema_ticket_sales',
                'available_tickets' => 3,
                'currency' => 'UAH',
                'mock_storage' => true,
            ],
        ]);
    }
}
