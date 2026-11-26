<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $discounts = Discount::factory()->count(10)->create();

        $events = Event::factory()
            ->count(5)
            ->has(
                Ticket::factory()
                    ->count(8)
                    ->hasAttached($discounts->random(2)),
                'tickets'
            )
            ->create();

        $discounts[0]->update([
            'code' => 'STUDENT20',
            'name' => 'Студентська знижка',
            'type' => 'percentage',
            'value' => 20,
        ]);
        $discounts[1]->update([
            'code' => 'PREMIERE100',
            'name' => 'Прем’єрний бонус',
            'type' => 'fixed',
            'value' => 100,
        ]);

        $events[0]->update([
            'name' => 'Прем’єра «Дюна: Частина третя»',
            'slug' => 'dune-part-three-premiere',
            'venue' => 'Зала IMAX 1',
        ]);
        $events[1]->update([
            'name' => 'Ніч українського кіно',
            'slug' => 'ukrainian-cinema-night',
            'venue' => 'Зала 3',
        ]);

        $demoTicket = $events[0]->tickets()->firstOrFail();
        $demoTicket->update([
            'code' => 'TKT-DEMO-2026',
            'seat_number' => 'F-16',
            'price' => 345.50,
            'customer_email' => 'viewer@example.com',
            'status' => 'reserved',
        ]);
        $demoTicket->discounts()->sync($discounts->take(2)->modelKeys());
    }
}
