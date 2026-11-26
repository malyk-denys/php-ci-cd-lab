<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tickets = Ticket::query()
            ->with([
                'event:id,name,venue,starts_at',
                'discounts:id,code,name,type,value',
            ])
            ->when(
                $request->filled('event_id'),
                fn ($query) => $query->where('event_id', $request->integer('event_id'))
            )
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->string('status')->toString())
            )
            ->orderBy('id')
            ->paginate(min(max($request->integer('per_page', 15), 1), 100))
            ->withQueryString();

        return response()->json([
            'status' => 'success',
            'data' => $tickets,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'code' => ['required', 'string', 'max:64', 'unique:tickets,code'],
            'seat_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('tickets', 'seat_number')
                    ->where('event_id', $request->integer('event_id')),
            ],
            'price' => ['required', 'numeric', 'min:0.01'],
            'customer_email' => ['required', 'email', 'max:255'],
            'status' => ['nullable', Rule::in(['available', 'reserved', 'sold', 'cancelled'])],
            'sold_at' => ['nullable', 'date'],
            'discounts' => ['nullable', 'array'],
            'discounts.*' => ['integer', 'distinct', 'exists:discounts,id'],
        ]);

        $ticket = DB::transaction(function () use ($validated): Ticket {
            $ticket = Ticket::create(Arr::except($validated, ['discounts']));
            $ticket->discounts()->sync($validated['discounts'] ?? []);

            return $ticket;
        });

        $ticket->load(['event:id,name,venue,starts_at', 'discounts:id,code,name,type,value']);

        return response()->json([
            'status' => 'created',
            'data' => $ticket,
        ], 201);
    }

    public function show(Ticket $ticket): JsonResponse
    {
        $ticket->load(['event', 'discounts']);

        return response()->json([
            'status' => 'success',
            'data' => $ticket,
        ]);
    }

    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $eventId = $request->integer('event_id', (int) $ticket->event_id);

        $validated = $request->validate([
            'event_id' => ['sometimes', 'required', 'integer', 'exists:events,id'],
            'code' => ['sometimes', 'required', 'string', 'max:64', Rule::unique('tickets', 'code')->ignore($ticket->id)],
            'seat_number' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('tickets', 'seat_number')->where('event_id', $eventId)->ignore($ticket->id),
            ],
            'price' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'customer_email' => ['sometimes', 'required', 'email', 'max:255'],
            'status' => ['sometimes', Rule::in(['available', 'reserved', 'sold', 'cancelled'])],
            'sold_at' => ['nullable', 'date'],
            'discounts' => ['sometimes', 'array'],
            'discounts.*' => ['integer', 'distinct', 'exists:discounts,id'],
        ]);

        DB::transaction(function () use ($ticket, $validated): void {
            $ticket->update(Arr::except($validated, ['discounts']));

            if (array_key_exists('discounts', $validated)) {
                $ticket->discounts()->sync($validated['discounts']);
            }
        });

        $ticket->load(['event:id,name,venue,starts_at', 'discounts:id,code,name,type,value']);

        return response()->json([
            'status' => 'success',
            'data' => $ticket,
        ]);
    }

    public function destroy(Ticket $ticket): JsonResponse
    {
        $ticket->delete();

        return response()->json(['status' => 'deleted']);
    }
}
