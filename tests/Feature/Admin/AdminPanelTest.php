<?php

namespace Tests\Feature\Admin;

use App\Mail\NewsletterBroadcast;
use App\Models\Event;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_non_admin_ne_peut_pas_acceder_au_panel(): void
    {
        $organizer = User::factory()->organizer()->create();

        $this->actingAs($organizer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_l_admin_voit_le_tableau_de_bord(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Event Pulse — Admin');
    }

    private function createEvent(User $organizer, array $attributes = []): Event
    {
        return Event::create(array_merge([
            'user_id' => $organizer->id,
            'title' => 'Concert Test',
            'slug' => 'concert-test-'.uniqid(),
            'description' => 'Description test.',
            'location' => 'Kinshasa',
            'category' => 'music',
            'event_date' => now()->addDays(10),
            'capacity' => 50,
            'price' => 100.00,
            'status' => 'published',
            'is_paid' => true,
            'placement_mode' => Event::PLACEMENT_STANDING,
        ], $attributes));
    }

    public function test_l_admin_peut_supprimer_un_organisateur(): void
    {
        $admin = User::factory()->admin()->create();
        $organizer = User::factory()->organizer()->create();
        $this->createEvent($organizer);

        $this->actingAs($admin)
            ->delete(route('admin.organizers.destroy', $organizer), ['confirm' => 'DELETE'])
            ->assertRedirect(route('admin.organizers.index'));

        $this->assertSoftDeleted('users', ['id' => $organizer->id]);
        $this->assertSame(0, Event::count());
    }

    public function test_l_admin_peut_envoyer_la_newsletter(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $organizer = User::factory()->organizer()->create();
        NewsletterSubscriber::create(['email' => 'fan@example.com']);

        $this->createEvent($organizer, ['updated_at' => now()]);

        $this->actingAs($admin)
            ->post(route('admin.newsletter.send'), ['days' => 7])
            ->assertRedirect();

        Mail::assertQueued(NewsletterBroadcast::class, fn ($mail) => $mail->hasTo('fan@example.com'));
    }

    public function test_le_dashboard_redirige_les_admins(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));
    }
}
