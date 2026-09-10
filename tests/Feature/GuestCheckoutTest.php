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

    private function paidPublishedEvent(): Event
    {
        $organizer = User::factory()->create([
            'role' => 'organizer',
            'organizer_status' => 'approved',
            'organizer_terms_accepted_at' => now(),
            'organizer_terms_version' => \App\Support\OrganizerTerms::version(),
        ]);

        $event = Event::create([
            'user_id' => $organizer->id,
            'title' => 'Concert Payant',
            'slug' => 'concert-payant-'.uniqid(),
            'description' => 'Test checkout payant.',
            'location' => 'Kinshasa',
            'category' => 'music',
            'event_date' => now()->addWeek(),
            'capacity' => 100,
            'price' => 15,
            'status' => 'published',
            'is_paid' => true,
            'placement_mode' => Event::PLACEMENT_STANDING,
            'accepted_payment_methods' => ['cash', 'card'],
        ]);

        $event->ticketTypes()->create([
            'name' => 'Standard',
            'price' => 15,
            'quantity' => 100,
            'is_active' => true,
        ]);

        return $event->fresh('ticketTypes');
    }

    public function test_guest_free_checkout_shows_login_prompt(): void
    {
        $event = $this->publishedEvent();
        $typeId = $event->ticketTypes->first()->id;

        $this->post(route('tickets.store', $event), [
            'ticket_type_id' => $typeId,
            'quantity' => 1,
        ])->assertRedirect(route('tickets.checkout.confirm', $event));

        $this->get(route('tickets.checkout.confirm', $event))
            ->assertOk()
            ->assertSee(__('Free checkout login required'), false)
            ->assertSee(__('Create participant account'), false)
            ->assertSee('accueil', false);

        $this->assertGuest();
    }

    public function test_guest_free_checkout_completes_after_login(): void
    {
        $event = $this->publishedEvent();
        $typeId = $event->ticketTypes->first()->id;
        $participant = User::factory()->create(['role' => 'participant']);

        $this->post(route('tickets.store', $event), [
            'ticket_type_id' => $typeId,
            'quantity' => 1,
        ])->assertRedirect(route('tickets.checkout.confirm', $event));

        $this->post(route('tickets.checkout.complete', $event))
            ->assertRedirect(route('login'))
            ->assertSessionHas('info');

        $this->actingAs($participant)
            ->post(route('tickets.checkout.complete', $event))
            ->assertRedirect(route('tickets.index'))
            ->assertSessionHas('success');

        $this->assertSame(1, $event->tickets()->count());
    }

    public function test_guest_paid_checkout_redirects_to_confirm_without_account(): void
    {
        $event = $this->paidPublishedEvent();
        $typeId = $event->ticketTypes->first()->id;

        $this->post(route('tickets.store', $event), [
            'ticket_type_id' => $typeId,
            'quantity' => 2,
        ])
            ->assertRedirect(route('tickets.checkout.confirm', $event));

        $this->assertGuest();
        $this->assertSame(0, $event->tickets()->count());
    }

    public function test_guest_paid_checkout_completes_after_login(): void
    {
        $event = $this->paidPublishedEvent();
        $typeId = $event->ticketTypes->first()->id;
        $participant = User::factory()->create(['role' => 'participant']);

        $this->post(route('tickets.store', $event), [
            'ticket_type_id' => $typeId,
            'quantity' => 1,
        ])->assertRedirect(route('tickets.checkout.confirm', $event));

        $this->post(route('tickets.checkout.payment', $event), [
            'payment_method' => 'card',
        ])->assertRedirect(route('tickets.checkout.confirm', $event));

        $this->actingAs($participant)
            ->get(route('tickets.checkout.confirm', $event))
            ->assertOk()
            ->assertSee(__('Paid checkout confirm button'), false);

        $this->actingAs($participant)
            ->post(route('tickets.checkout.complete', $event), [
                'payment_method' => 'card',
            ])
            ->assertRedirect(route('tickets.index'))
            ->assertSessionHas('success');

        $this->assertSame(1, $event->tickets()->count());
        $this->assertDatabaseHas('tickets', [
            'event_id' => $event->id,
            'user_id' => $participant->id,
            'payment_method' => 'card',
        ]);
    }
}
