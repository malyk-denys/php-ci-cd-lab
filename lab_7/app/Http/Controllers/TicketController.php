<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class TicketController extends Controller
{
    public function index(Request $request): View|JsonResponse
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
            ->orderByDesc('id')
            ->paginate(
                $request->expectsJson()
                    ? min(max($request->integer('per_page', 15), 1), 100)
                    : 10
            )
            ->withQueryString();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $tickets,
            ]);
        }

        $events = Event::query()->orderBy('starts_at')->get(['id', 'name', 'starts_at']);

        return view('tickets.index', compact('tickets', 'events'));
    }

    public function create(): View
    {
        return view('tickets.create', $this->formOptions());
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate($this->rules($request));

        $ticket = DB::transaction(function () use ($validated): Ticket {
            $ticket = Ticket::create(Arr::except($validated, ['discounts']));
            $ticket->discounts()->sync($validated['discounts'] ?? []);

            return $ticket;
        });

        if ($request->expectsJson()) {
            $ticket->load(['event:id,name,venue,starts_at', 'discounts:id,code,name,type,value']);

            return response()->json([
                'status' => 'created',
                'data' => $ticket,
            ], 201);
        }

        return redirect()
            ->route('tickets.index')
            ->with('success', "Квиток {$ticket->code} успішно створено.");
    }

    public function show(Request $request, Ticket $ticket): View|JsonResponse
    {
        $ticket->load(['event', 'discounts']);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $ticket,
            ]);
        }

        return view('tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket): View
    {
        $ticket->load('discounts');

        return view('tickets.edit', [
            'ticket' => $ticket,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, Ticket $ticket): JsonResponse|RedirectResponse
    {
        $validated = $request->validate($this->rules($request, $ticket));

        DB::transaction(function () use ($ticket, $validated, $request): void {
            $ticket->update(Arr::except($validated, ['discounts']));

            if (! $request->expectsJson() || array_key_exists('discounts', $validated)) {
                $ticket->discounts()->sync($validated['discounts'] ?? []);
            }
        });

        if ($request->expectsJson()) {
            $ticket->load(['event:id,name,venue,starts_at', 'discounts:id,code,name,type,value']);

            return response()->json([
                'status' => 'success',
                'data' => $ticket,
            ]);
        }

        return redirect()
            ->route('tickets.index')
            ->with('success', "Квиток {$ticket->code} успішно оновлено.");
    }

    public function destroy(Request $request, Ticket $ticket): JsonResponse|RedirectResponse
    {
        $code = $ticket->code;
        $ticket->delete();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'deleted']);
        }

        return redirect()
            ->route('tickets.index')
            ->with('success', "Квиток {$code} успішно видалено.");
    }

    /** @return array{events: Collection, discounts: Collection} */
    private function formOptions(): array
    {
        return [
            'events' => Event::query()->orderBy('starts_at')->get(),
            'discounts' => Discount::query()->orderBy('code')->get(),
        ];
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(Request $request, ?Ticket $ticket = null): array
    {
        $presence = $ticket === null || ! $request->expectsJson() ? 'required' : 'sometimes';
        $eventId = $request->integer('event_id', (int) $ticket?->event_id);

        return [
            'event_id' => [$presence, 'integer', 'exists:events,id'],
            'code' => [
                $presence,
                'string',
                'max:64',
                Rule::unique('tickets', 'code')->ignore($ticket?->id),
            ],
            'seat_number' => [
                $presence,
                'string',
                'max:20',
                Rule::unique('tickets', 'seat_number')
                    ->where('event_id', $eventId)
                    ->ignore($ticket?->id),
            ],
            'price' => [$presence, 'numeric', 'min:0.01'],
            'customer_email' => [$presence, 'email', 'max:255'],
            'status' => [
                $ticket === null ? 'nullable' : ($request->expectsJson() ? 'sometimes' : 'required'),
                Rule::in(['available', 'reserved', 'sold', 'cancelled']),
            ],
            'sold_at' => ['nullable', 'date'],
            'discounts' => ['nullable', 'array'],
            'discounts.*' => ['integer', 'distinct', 'exists:discounts,id'],
        ];
    }
}
