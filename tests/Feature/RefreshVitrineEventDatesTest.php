<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Support\VitrineEvents;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefreshVitrineEventDatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_commande_met_a_jour_les_evenements_vitrine_dont_la_date_est_passee(): void
    {
        $this->travelTo(now()->startOfDay());

        $organizer = User::factory()->create([
            'email' => VitrineEvents::ORGANIZER_EMAIL,
            'role' => 'organizer',
        ]);

        $definition = VitrineEvents::definitions()[0];
        $slug = VitrineEvents::slug($definition);

        $event = Event::create([
            'user_id' => $organizer->id,
            'title' => $definition['title'],
            'slug' => $slug,
            'description' => 'Description vitrine.',
            'location' => $definition['location'],
            'category' => $definition['category'],
            'event_date' => now()->subDays(3),
            'capacity' => 200,
            'price' => 0,
            'status' => 'published',
            'is_paid' => false,
            'placement_mode' => Event::PLACEMENT_STANDING,
        ]);

        $this->artisan('eventpulse:refresh-vitrine-dates')
            ->assertSuccessful();

        $event->refresh();

        $this->assertTrue($event->event_date->isFuture());
        $this->assertTrue(
            $event->event_date->equalTo(VitrineEvents::scheduledAt($definition))
        );
    }

    public function test_la_commande_ne_modifie_pas_les_dates_futures_sans_force(): void
    {
        $organizer = User::factory()->create([
            'email' => VitrineEvents::ORGANIZER_EMAIL,
            'role' => 'organizer',
        ]);

        $definition = VitrineEvents::definitions()[1];
        $slug = VitrineEvents::slug($definition);
        $futureDate = now()->addDays(30);

        $event = Event::create([
            'user_id' => $organizer->id,
            'title' => $definition['title'],
            'slug' => $slug,
            'description' => 'Description vitrine.',
            'location' => $definition['location'],
            'category' => $definition['category'],
            'event_date' => $futureDate,
            'capacity' => 150,
            'price' => 0,
            'status' => 'published',
            'is_paid' => false,
            'placement_mode' => Event::PLACEMENT_STANDING,
        ]);

        $expected = $event->fresh()->event_date->copy();

        $this->artisan('eventpulse:refresh-vitrine-dates')
            ->assertSuccessful();

        $this->assertTrue($event->fresh()->event_date->equalTo($expected));
    }

    public function test_l_option_force_recalcule_toutes_les_dates_vitrine(): void
    {
        $this->travelTo(now()->startOfDay());

        $organizer = User::factory()->create([
            'email' => VitrineEvents::ORGANIZER_EMAIL,
            'role' => 'organizer',
        ]);

        $definition = VitrineEvents::definitions()[2];
        $slug = VitrineEvents::slug($definition);
        $futureDate = now()->addDays(30);

        $event = Event::create([
            'user_id' => $organizer->id,
            'title' => $definition['title'],
            'slug' => $slug,
            'description' => 'Description vitrine.',
            'location' => $definition['location'],
            'category' => $definition['category'],
            'event_date' => $futureDate,
            'capacity' => 580,
            'price' => 15000,
            'status' => 'published',
            'is_paid' => false,
            'placement_mode' => Event::PLACEMENT_STANDING,
        ]);

        $this->artisan('eventpulse:refresh-vitrine-dates --force')
            ->assertSuccessful();

        $event->refresh();

        $this->assertTrue(
            $event->event_date->equalTo(VitrineEvents::scheduledAt($definition))
        );
    }
}
