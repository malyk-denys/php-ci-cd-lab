<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Discount;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TicketDatabaseTest extends TestCase
{
    use RefreshDatabase;

    private const PARTNER_KEY = 'database-test-partner-key';

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.ticket_partner.api_key', self::PARTNER_KEY);
    }

    public function test_can_fetch_tickets_with_event_and_discounts(): void
    {
        $event = Event::factory()->create(['name' => 'Interstellar']);
        $discount = Discount::factory()->create(['name' => 'Студентська знижка']);
        $ticket = Ticket::factory()->for($event)->create([
            'code' => 'TKT-FETCH-001',
            'status' => 'available',
        ]);
        $ticket->discounts()->attach($discount);

        $this->getJson('/tickets')
            ->assertOk()
            ->assertJsonPath('data.data.0.code', 'TKT-FETCH-001')
            ->assertJsonPath('data.data.0.event.name', 'Interstellar')
            ->assertJsonPath('data.data.0.discounts.0.name', 'Студентська знижка');
    }

    public function test_can_create_ticket_with_event_and_discounts_in_database(): void
    {
        $event = Event::factory()->create();
        $discounts = Discount::factory()->count(2)->create();

        $response = $this->withHeader('X-Partner-Api-Key', self::PARTNER_KEY)
            ->postJson('/tickets', [
                'event_id' => $event->id,
                'code' => 'TKT-CREATE-001',
                'seat_number' => 'A-12',
                'price' => 320.50,
                'customer_email' => 'buyer@example.com',
                'status' => 'sold',
                'discounts' => $discounts->modelKeys(),
            ])
            ->assertCreated()
            ->assertJsonCount(2, 'data.discounts');

        $ticketId = $response->json('data.id');
        $this->assertDatabaseHas('tickets', [
            'id' => $ticketId,
            'event_id' => $event->id,
            'code' => 'TKT-CREATE-001',
        ]);

        foreach ($discounts as $discount) {
            $this->assertDatabaseHas('discount_ticket', [
                'ticket_id' => $ticketId,
                'discount_id' => $discount->id,
            ]);
        }
    }

    public function test_update_synchronizes_many_to_many_relation(): void
    {
        $ticket = Ticket::factory()->create();
        $discounts = Discount::factory()->count(3)->create();
        $ticket->discounts()->attach($discounts->take(2));

        $this->withHeader('X-Partner-Api-Key', self::PARTNER_KEY)
            ->putJson('/tickets/'.$ticket->id, [
                'price' => 199.99,
                'discounts' => [$discounts->last()->id],
            ])
            ->assertOk()
            ->assertJsonPath('data.price', 199.99)
            ->assertJsonCount(1, 'data.discounts');

        $this->assertDatabaseCount('discount_ticket', 1);
        $this->assertDatabaseHas('discount_ticket', [
            'ticket_id' => $ticket->id,
            'discount_id' => $discounts->last()->id,
        ]);
    }

    public function test_event_delete_cascades_tickets_and_pivot_rows(): void
    {
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->for($event)->create();
        $discount = Discount::factory()->create();
        $ticket->discounts()->attach($discount);

        $event->delete();

        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
        $this->assertDatabaseMissing('discount_ticket', ['ticket_id' => $ticket->id]);
        $this->assertDatabaseHas('discounts', ['id' => $discount->id]);
    }

    public function test_same_seat_cannot_be_created_twice_for_one_event(): void
    {
        $event = Event::factory()->create();
        Ticket::factory()->for($event)->create(['seat_number' => 'B-07']);

        $this->withHeader('X-Partner-Api-Key', self::PARTNER_KEY)
            ->postJson('/tickets', [
                'event_id' => $event->id,
                'code' => 'TKT-DUPLICATE-SEAT',
                'seat_number' => 'B-07',
                'price' => 180,
                'customer_email' => 'buyer@example.com',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('seat_number');
    }
}
