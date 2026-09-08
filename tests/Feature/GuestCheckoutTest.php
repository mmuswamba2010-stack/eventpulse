<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function publishedEvent(): Event
    {
        $organizer = User::factory()->create([
            'role' => 'organizer',
            'organizer_status' => 'approved',
            'organizer_terms_accepted_at' => now(),
            'organizer_terms_version' => \App\Support\OrganizerTerms::version(),
        ]);

        $event = Event::create([
            'user_id' => $organizer->id,
            'title' => 'Concert Express',
            'slug' => 'concert-express-'.uniqid(),
            'description' => 'Test checkout invité.',
            'location' => 'Kinshasa',
            'category' => 'music',
            'event_date' => now()->addWeek(),
            'capacity' => 100,
            'price' => 0,
            'status' => 'published',
            'is_paid' => true,
            'placement_mode' => Event::PLACEMENT_STANDING,
            'accepted_payment_methods' => [],
        ]);

        $event->ticketTypes()->create([
            'name' => 'Standard',
            'price' => 0,
            'quantity' => 100,
            'is_active' => true,
        ]);

        return $event->fresh('ticketTypes');
    }

    public function test_guest_can_book_without_prior_registration(): void
    {
        $event = $this->publishedEvent();
        $typeId = $event->ticketTypes->first()->id;

        $this->post(route('tickets.store', $event), [
            'guest_name' => 'Marie Client',
            'guest_email' => 'marie@example.com',
            'guest_phone' => '+243812345678',
            'ticket_type_id' => $typeId,
            'quantity' => 1,
        ])
            ->assertRedirect(route('tickets.index'))
            ->assertSessionHas('success')
            ->assertSessionHas('info');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'marie@example.com',
            'role' => 'participant',
        ]);
        $this->assertSame(1, $event->tickets()->count());
    }

    public function test_guest_with_existing_email_must_login(): void
    {
        $event = $this->publishedEvent();
        User::factory()->create(['email' => 'existe@example.com', 'role' => 'participant']);
        $typeId = $event->ticketTypes->first()->id;

        $this->from(route('events.show', $event->slug))
            ->post(route('tickets.store', $event), [
                'guest_name' => 'Dupont',
                'guest_email' => 'existe@example.com',
                'guest_phone' => '+243812345678',
                'ticket_type_id' => $typeId,
                'quantity' => 1,
            ])
            ->assertRedirect(route('events.show', $event->slug))
            ->assertSessionHasErrors('guest_email');

        $this->assertGuest();
    }
}
