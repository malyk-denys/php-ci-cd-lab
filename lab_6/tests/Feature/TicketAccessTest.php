<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TicketAccessTest extends TestCase
{
    use RefreshDatabase;

    private const PARTNER_KEY = 'automated-test-partner-key';

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.ticket_partner.api_key', self::PARTNER_KEY);
        $this->event = Event::factory()->create();
    }

    public function test_ticket_index_returns_ticket_collection(): void
    {
        Ticket::factory()->count(3)->for($this->event)->create();

        $this->getJson('/tickets')
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(3, 'data.data');
    }

    public function test_existing_ticket_can_be_shown(): void
    {
        $ticket = Ticket::factory()->for($this->event)->create(['seat_number' => 'C-08']);

        $this->getJson('/tickets/'.$ticket->id)
            ->assertOk()
            ->assertJsonPath('data.id', $ticket->id)
            ->assertJsonPath('data.seat_number', 'C-08')
            ->assertJsonPath('data.event.id', $this->event->id);
    }

    public function test_missing_ticket_returns_not_found(): void
    {
        $this->getJson('/tickets/999')->assertNotFound();
    }

    public function test_request_without_partner_key_is_blocked_by_middleware(): void
    {
        $this->postJson('/tickets', $this->validTicket())
            ->assertForbidden()
            ->assertJsonPath('status', 'access_denied');
    }

    public function test_request_with_invalid_partner_key_is_blocked_by_middleware(): void
    {
        $this->withHeader('X-Partner-Api-Key', 'wrong-key')
            ->postJson('/tickets', $this->validTicket())
            ->assertForbidden()
            ->assertJsonPath('status', 'access_denied');
    }

    public function test_request_with_valid_partner_key_creates_ticket(): void
    {
        $this->withHeader('X-Partner-Api-Key', self::PARTNER_KEY)
            ->postJson('/tickets', $this->validTicket())
            ->assertCreated()
            ->assertHeader('X-Security-Policy', 'Partner-Key-Enforced')
            ->assertJsonPath('status', 'created')
            ->assertJsonPath('data.event.id', $this->event->id)
            ->assertJsonPath('data.customer_email', 'denys@example.com');

        $this->assertDatabaseHas('tickets', [
            'code' => 'TKT-LAB6-001',
            'event_id' => $this->event->id,
        ]);
    }

    public function test_ticket_fields_are_validated_after_authorization(): void
    {
        $this->withHeader('X-Partner-Api-Key', self::PARTNER_KEY)
            ->postJson('/tickets', [
                'event_id' => 999,
                'code' => '',
                'seat_number' => '',
                'price' => 0,
                'customer_email' => 'not-an-email',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'event_id',
                'code',
                'seat_number',
                'price',
                'customer_email',
            ]);
    }

    private function validTicket(): array
    {
        return [
            'event_id' => $this->event->id,
            'code' => 'TKT-LAB6-001',
            'seat_number' => 'D-15',
            'price' => 240,
            'customer_email' => 'denys@example.com',
            'status' => 'reserved',
        ];
    }
}
