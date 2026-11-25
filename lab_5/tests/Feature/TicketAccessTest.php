<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class TicketAccessTest extends TestCase
{
    private const PARTNER_KEY = 'automated-test-partner-key';

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.ticket_partner.api_key', self::PARTNER_KEY);
    }

    public function test_ticket_index_returns_ticket_collection(): void
    {
        $this->getJson('/tickets')
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(3, 'data');
    }

    public function test_existing_ticket_can_be_shown(): void
    {
        $this->getJson('/tickets/2')
            ->assertOk()
            ->assertJsonPath('data.id', 2)
            ->assertJsonPath('data.seat_number', 'C-08');
    }

    public function test_missing_ticket_returns_not_found(): void
    {
        $this->getJson('/tickets/999')
            ->assertNotFound()
            ->assertJsonPath('status', 'error');
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
            ->assertJsonPath('data.id', 4)
            ->assertJsonPath('data.customer_email', 'denys@example.com');
    }

    public function test_ticket_fields_are_validated_after_authorization(): void
    {
        $this->withHeader('X-Partner-Api-Key', self::PARTNER_KEY)
            ->postJson('/tickets', [
                'event_name' => '',
                'seat_number' => '',
                'price' => 0,
                'customer_email' => 'not-an-email',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'event_name',
                'seat_number',
                'price',
                'customer_email',
            ]);
    }

    /** @return array{event_name: string, seat_number: string, price: int, customer_email: string} */
    private function validTicket(): array
    {
        return [
            'event_name' => 'Interstellar',
            'seat_number' => 'D-15',
            'price' => 240,
            'customer_email' => 'denys@example.com',
        ];
    }
}
