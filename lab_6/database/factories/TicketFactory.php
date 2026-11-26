<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Ticket> */
final class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'code' => strtoupper(fake()->unique()->bothify('TKT-####-????')),
            'seat_number' => fake()->randomLetter().'-'.fake()->unique()->numberBetween(1, 5000),
            'price' => fake()->randomFloat(2, 120, 850),
            'customer_email' => fake()->unique()->safeEmail(),
            'status' => fake()->randomElement(['available', 'reserved', 'sold']),
            'sold_at' => null,
        ];
    }
}
