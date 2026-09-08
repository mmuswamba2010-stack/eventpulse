<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTypeSaleTest extends TestCase
{
    use RefreshDatabase;

    private function publishedEvent(array $typeOverrides = []): Event
    {
        $organizer = User::factory()->create([
            'role' => 'organizer',
            'organizer_status' => 'approved',
            'organizer_terms_accepted_at' => now(),
            'organizer_terms_version' => \App\Support\OrganizerTerms::version(),
        ]);

        $event = Event::create([
            'user_id' => $organizer->id,
            'title' => 'Festival Billetterie',
            'slug' => 'festival-billetterie-'.uniqid(),
            'description' => 'Test billetterie multi-passes.',
            'location' => 'Kinshasa',
            'category' => 'music',
            'event_date' => now()->addDays(14),
            'capacity' => 700,
            'price' => 10,
            'status' => 'published',
            'is_paid' => true,
            'placement_mode' => Event::PLACEMENT_STANDING,
            'accepted_payment_methods' => ['cash'],
        ]);

        $event->ticketTypes()->createMany([
            array_merge([
                'name' => 'Early Bird',
                'price' => 10,
                'quantity' => 100,
                'is_seated' => false,
                'is_active' => true,
                'sale_starts_at' => now()->subDay(),
                'sale_ends_at' => now()->addDays(3),
            ], $typeOverrides),
            [
                'name' => 'Standard',
                'price' => 20,
                'quantity' => 500,
                'is_seated' => false,
                'is_active' => true,
            ],
            [
                'name' => 'VIP',
                'price' => 50,
                'quantity' => 100,
                'is_seated' => false,
                'is_active' => true,
            ],
        ]);

        return $event->fresh('ticketTypes');
    }

    public function test_event_page_lists_multiple_ticket_tiers(): void
    {
        $event = $this->publishedEvent();

        $this->get(route('events.show', $event->slug))
            ->assertOk()
            ->assertSee('Early Bird', false)
            ->assertSee('Standard', false)
            ->assertSee('VIP', false);
    }

    public function test_inactive_ticket_type_cannot_be_purchased(): void
    {
        $event = $this->publishedEvent(['is_active' => false]);
        $participant = User::factory()->create(['role' => 'participant']);
        $type = $event->ticketTypes->firstWhere('name', 'Early Bird');

        $this->actingAs($participant)
            ->from(route('events.show', $event->slug))
            ->post(route('tickets.store', $event), [
                'ticket_type_id' => $type->id,
                'quantity' => 1,
                'payment_method' => 'cash',
            ])
            ->assertRedirect(route('events.show', $event->slug))
            ->assertSessionHas('error');

        $this->assertSame(0, $event->tickets()->count());
    }

    public function test_upcoming_ticket_type_cannot_be_purchased(): void
    {
        $event = $this->publishedEvent([
            'sale_starts_at' => now()->addDays(2),
            'sale_ends_at' => now()->addDays(5),
        ]);
        $participant = User::factory()->create(['role' => 'participant']);
        $type = $event->ticketTypes->firstWhere('name', 'Early Bird');

        $this->assertSame(TicketType::SALE_UPCOMING, $type->saleStatus());

        $this->actingAs($participant)
            ->from(route('events.show', $event->slug))
            ->post(route('tickets.store', $event), [
                'ticket_type_id' => $type->id,
                'quantity' => 1,
                'payment_method' => 'cash',
            ])
            ->assertSessionHas('error');
    }

    public function test_active_ticket_type_can_be_purchased(): void
    {
        $event = $this->publishedEvent();
        $participant = User::factory()->create(['role' => 'participant']);
        $type = $event->ticketTypes->firstWhere('name', 'VIP');

        $this->actingAs($participant)
            ->post(route('tickets.store', $event), [
                'ticket_type_id' => $type->id,
                'quantity' => 1,
                'payment_method' => 'cash',
            ])
            ->assertRedirect(route('tickets.index'));

        $this->assertSame(1, $event->tickets()->where('ticket_type_id', $type->id)->count());
    }
}
