<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Event> */
final class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 9999),
            'venue' => fake()->city().' Cinema Hall',
            'starts_at' => fake()->dateTimeBetween('+1 day', '+4 months'),
            'status' => fake()->randomElement(['scheduled', 'scheduled', 'scheduled', 'cancelled']),
            'description' => fake()->sentence(),
        ];
    }
}
