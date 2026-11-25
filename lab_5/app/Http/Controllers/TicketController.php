<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TicketController extends Controller
{
    /** @var list<array{id: int, event_name: string, seat_number: string, price: float, customer_email: string}> */
    private array $tickets = [
        ['id' => 1, 'event_name' => 'Interstellar', 'seat_number' => 'A-12', 'price' => 220.00, 'customer_email' => 'anna@example.com'],
        ['id' => 2, 'event_name' => 'Dune Part Two', 'seat_number' => 'C-08', 'price' => 260.00, 'customer_email' => 'oleh@example.com'],
        ['id' => 3, 'event_name' => 'The Wild Robot', 'seat_number' => 'B-04', 'price' => 180.00, 'customer_email' => 'marta@example.com'],
    ];

    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->tickets,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_name' => ['required', 'string', 'min:2', 'max:255'],
            'seat_number' => ['required', 'string', 'max:20'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'customer_email' => ['required', 'email', 'max:255'],
        ]);

        $ticket = [
            'id' => count($this->tickets) + 1,
            'event_name' => $validated['event_name'],
            'seat_number' => $validated['seat_number'],
            'price' => (float) $validated['price'],
            'customer_email' => $validated['customer_email'],
        ];

        $this->tickets[] = $ticket;

        return response()->json([
            'status' => 'created',
            'data' => $ticket,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $ticketId = (int) $id;

        foreach ($this->tickets as $ticket) {
            if ($ticket['id'] === $ticketId) {
                return response()->json([
                    'status' => 'success',
                    'data' => $ticket,
                ]);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Квиток із зазначеним ідентифікатором не знайдено.',
        ], 404);
    }
}
