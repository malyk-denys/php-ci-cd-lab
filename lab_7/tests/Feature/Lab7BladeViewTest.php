<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Discount;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class Lab7BladeViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_catalog_displays_eager_loaded_relations(): void
    {
        $event = Event::factory()->create(['name' => 'Прем’єра Дюни']);
        $discount = Discount::factory()->create(['code' => 'STUDENT20']);
        $ticket = Ticket::factory()->for($event)->create([
            'code' => 'TKT-BLADE-001',
            'customer_email' => 'viewer@example.com',
        ]);
        $ticket->discounts()->attach($discount);

        $this->get(route('tickets.index'))
            ->assertOk()
            ->assertViewIs('tickets.index')
            ->assertSee('TKT-BLADE-001')
            ->assertSee('Прем’єра Дюни')
            ->assertSee('STUDENT20')
            ->assertSee('Додати квиток');
    }

    public function test_create_and_edit_forms_contain_csrf_relations_and_method_spoofing(): void
    {
        $event = Event::factory()->create();
        $discount = Discount::factory()->create();
        $ticket = Ticket::factory()->for($event)->create();
        $ticket->discounts()->attach($discount);

        $this->get(route('tickets.create'))
            ->assertOk()
            ->assertSee('name="_token"', false)
            ->assertSee('name="event_id"', false)
            ->assertSee('name="discounts[]"', false);

        $this->get(route('tickets.edit', $ticket))
            ->assertOk()
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('value="'.$discount->id.'"', false)
            ->assertSee('checked', false);
    }

    public function test_ticket_form_validates_and_preserves_errors_in_session(): void
    {
        $this->from(route('tickets.create'))
            ->post(route('tickets.store'), [
                'event_id' => 999,
                'code' => '',
                'seat_number' => '',
                'price' => 'invalid',
                'customer_email' => 'not-an-email',
                'status' => 'unknown',
            ])
            ->assertRedirect(route('tickets.create'))
            ->assertSessionHasErrors([
                'event_id',
                'code',
                'seat_number',
                'price',
                'customer_email',
                'status',
            ]);
    }

    public function test_browser_crud_creates_updates_relations_and_deletes_ticket(): void
    {
        $event = Event::factory()->create();
        $discounts = Discount::factory()->count(2)->create();

        $this->post(route('tickets.store'), [
            'event_id' => $event->id,
            'code' => 'TKT-WEB-001',
            'seat_number' => 'F-16',
            'price' => 345.50,
            'customer_email' => 'browser@example.com',
            'status' => 'reserved',
            'discounts' => $discounts->modelKeys(),
        ])->assertRedirect(route('tickets.index'))
            ->assertSessionHas('success');

        $ticket = Ticket::query()->where('code', 'TKT-WEB-001')->firstOrFail();
        $this->assertCount(2, $ticket->discounts);

        $this->put(route('tickets.update', $ticket), [
            'event_id' => $event->id,
            'code' => 'TKT-WEB-001',
            'seat_number' => 'F-16',
            'price' => 399.99,
            'customer_email' => 'updated@example.com',
            'status' => 'sold',
            'discounts' => [$discounts->first()->id],
        ])->assertRedirect(route('tickets.index'))
            ->assertSessionHas('success');

        $ticket->refresh();
        $this->assertSame('updated@example.com', $ticket->customer_email);
        $this->assertSame([intval($discounts->first()->id)], $ticket->discounts()->pluck('discounts.id')->all());

        $this->delete(route('tickets.destroy', $ticket))
            ->assertRedirect(route('tickets.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    }

    public function test_event_and_discount_have_complete_blade_crud_pages(): void
    {
        $event = Event::factory()->create(['name' => 'Тестова подія']);
        $discount = Discount::factory()->create(['name' => 'Тестова знижка']);

        foreach ([
            route('events.index'),
            route('events.create'),
            route('events.show', $event),
            route('events.edit', $event),
            route('discounts.index'),
            route('discounts.create'),
            route('discounts.show', $discount),
            route('discounts.edit', $discount),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
