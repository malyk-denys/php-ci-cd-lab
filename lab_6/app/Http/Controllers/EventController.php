<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class EventController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => Event::query()->withCount('tickets')->orderBy('starts_at')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $event = Event::create($this->validatedData($request));

        return response()->json(['status' => 'created', 'data' => $event], 201);
    }

    public function show(Event $event): JsonResponse
    {
        $event->load(['tickets' => fn ($query) => $query->with('discounts')->orderBy('id')]);

        return response()->json(['status' => 'success', 'data' => $event]);
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $event->update($this->validatedData($request, $event));

        return response()->json(['status' => 'success', 'data' => $event->fresh()]);
    }

    public function destroy(Event $event): JsonResponse
    {
        $event->delete();

        return response()->json(['status' => 'deleted']);
    }

    /** @return array<string, mixed> */
    private function validatedData(Request $request, ?Event $event = null): array
    {
        $presence = $event === null ? 'required' : 'sometimes';

        return $request->validate([
            'name' => [$presence, 'string', 'min:2', 'max:255'],
            'slug' => [$presence, 'string', 'max:255', Rule::unique('events', 'slug')->ignore($event?->id)],
            'venue' => [$presence, 'string', 'min:2', 'max:255'],
            'starts_at' => [$presence, 'date'],
            'status' => ['sometimes', Rule::in(['scheduled', 'cancelled', 'completed'])],
            'description' => ['nullable', 'string'],
        ]);
    }
}
