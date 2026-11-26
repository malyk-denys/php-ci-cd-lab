<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class DiscountController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => Discount::query()->withCount('tickets')->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $discount = Discount::create($this->validatedData($request));

        return response()->json(['status' => 'created', 'data' => $discount], 201);
    }

    public function show(Discount $discount): JsonResponse
    {
        $discount->load(['tickets.event']);

        return response()->json(['status' => 'success', 'data' => $discount]);
    }

    public function update(Request $request, Discount $discount): JsonResponse
    {
        $discount->update($this->validatedData($request, $discount));

        return response()->json(['status' => 'success', 'data' => $discount->fresh()]);
    }

    public function destroy(Discount $discount): JsonResponse
    {
        $discount->delete();

        return response()->json(['status' => 'deleted']);
    }

    /** @return array<string, mixed> */
    private function validatedData(Request $request, ?Discount $discount = null): array
    {
        $presence = $discount === null ? 'required' : 'sometimes';

        return $request->validate([
            'code' => [$presence, 'string', 'max:64', Rule::unique('discounts', 'code')->ignore($discount?->id)],
            'name' => [$presence, 'string', 'min:2', 'max:255'],
            'type' => [$presence, Rule::in(['percentage', 'fixed'])],
            'value' => [$presence, 'numeric', 'min:0.01'],
            'active_from' => ['nullable', 'date'],
            'active_until' => ['nullable', 'date', 'after_or_equal:active_from'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
