<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class DiscountController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Discount::query()->withCount('tickets')->orderBy('code');

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $query->get()]);
        }

        $discounts = $query->paginate(10);

        return view('discounts.index', compact('discounts'));
    }

    public function create(): View
    {
        return view('discounts.create');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $discount = Discount::create($this->validatedData($request));

        if ($request->expectsJson()) {
            return response()->json(['status' => 'created', 'data' => $discount], 201);
        }

        return redirect()->route('discounts.index')->with('success', "Знижку {$discount->code} створено.");
    }

    public function show(Request $request, Discount $discount): View|JsonResponse
    {
        $discount->load(['tickets.event']);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $discount]);
        }

        return view('discounts.show', compact('discount'));
    }

    public function edit(Discount $discount): View
    {
        return view('discounts.edit', compact('discount'));
    }

    public function update(Request $request, Discount $discount): JsonResponse|RedirectResponse
    {
        $discount->update($this->validatedData($request, $discount));

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $discount->fresh()]);
        }

        return redirect()->route('discounts.index')->with('success', "Знижку {$discount->code} оновлено.");
    }

    public function destroy(Request $request, Discount $discount): JsonResponse|RedirectResponse
    {
        $code = $discount->code;
        $discount->delete();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'deleted']);
        }

        return redirect()->route('discounts.index')->with('success', "Знижку {$code} видалено.");
    }

    /** @return array<string, array<int, mixed>> */
    private function validatedData(Request $request, ?Discount $discount = null): array
    {
        $presence = $discount === null || ! $request->expectsJson() ? 'required' : 'sometimes';

        $validated = $request->validate([
            'code' => [
                $presence,
                'string',
                'max:64',
                Rule::unique('discounts', 'code')->ignore($discount?->id),
            ],
            'name' => [$presence, 'string', 'min:2', 'max:255'],
            'type' => [$presence, Rule::in(['percentage', 'fixed'])],
            'value' => [$presence, 'numeric', 'min:0.01'],
            'active_from' => ['nullable', 'date'],
            'active_until' => ['nullable', 'date', 'after_or_equal:active_from'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! $request->expectsJson()) {
            $validated['is_active'] = $request->boolean('is_active');
        }

        return $validated;
    }
}
