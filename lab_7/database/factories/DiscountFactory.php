<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Discount> */
final class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['percentage', 'fixed']);

        return [
            'code' => strtoupper(fake()->unique()->bothify('PROMO-####-??')),
            'name' => ucfirst(fake()->unique()->words(2, true)),
            'type' => $type,
            'value' => $type === 'percentage'
                ? fake()->randomFloat(2, 5, 40)
                : fake()->randomFloat(2, 20, 250),
            'active_from' => now()->subDays(7),
            'active_until' => now()->addMonths(2),
            'is_active' => fake()->boolean(85),
        ];
    }
}
