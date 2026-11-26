<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class EventController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Event::query()->withCount('tickets')->orderBy('starts_at');

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $query->get()]);
        }

        $events = $query->paginate(10);

        return view('events.index', compact('events'));
    }

    public function create(): View
    {
        return view('events.create');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $event = Event::create($this->validatedData($request));

        if ($request->expectsJson()) {
            return response()->json(['status' => 'created', 'data' => $event], 201);
        }

        return redirect()->route('events.index')->with('success', "Подію {$event->name} створено.");
    }

    public function show(Request $request, Event $event): View|JsonResponse
    {
        $event->load(['tickets' => fn ($query) => $query->with('discounts')->orderBy('id')]);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $event]);
        }

        return view('events.show', compact('event'));
    }

    public function edit(Event $event): View
    {
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event): JsonResponse|RedirectResponse
    {
        $event->update($this->validatedData($request, $event));

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $event->fresh()]);
        }

        return redirect()->route('events.index')->with('success', "Подію {$event->name} оновлено.");
    }

    public function destroy(Request $request, Event $event): JsonResponse|RedirectResponse
    {
        $name = $event->name;
        $event->delete();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'deleted']);
        }

        return redirect()->route('events.index')->with('success', "Подію {$name} видалено разом із її квитками.");
    }

    /** @return array<string, array<int, mixed>> */
    private function validatedData(Request $request, ?Event $event = null): array
    {
        $presence = $event === null || ! $request->expectsJson() ? 'required' : 'sometimes';

        return $request->validate([
            'name' => [$presence, 'string', 'min:2', 'max:255'],
            'slug' => [
                $presence,
                'string',
                'max:255',
                Rule::unique('events', 'slug')->ignore($event?->id),
            ],
            'venue' => [$presence, 'string', 'min:2', 'max:255'],
            'starts_at' => [$presence, 'date'],
            'status' => [
                $event === null ? 'nullable' : ($request->expectsJson() ? 'sometimes' : 'required'),
                Rule::in(['scheduled', 'cancelled', 'completed']),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
