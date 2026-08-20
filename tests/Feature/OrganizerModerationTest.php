<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizerModerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['eventpulse.organizer_moderation' => true]);
    }

    public function test_new_organizer_is_pending_and_cannot_access_dashboard(): void
    {
        $this->post('/register', [
            'name' => 'Pending Org',
            'email' => 'pending@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'organizer',
        ])->assertRedirect(route('organizer.pending'));

        $user = User::where('email', 'pending@example.com')->firstOrFail();
        $this->assertSame('pending', $user->organizer_status);

        $this->actingAs($user)
            ->get(route('organizer.dashboard'))
            ->assertRedirect(route('organizer.pending'));
    }

    public function test_admin_can_approve_pending_organizer(): void
    {
        $admin = User::factory()->admin()->create();
        $organizer = User::factory()->organizer()->create([
            'organizer_status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.organizers.approve', $organizer))
            ->assertRedirect(route('admin.organizers.index'));

        $this->assertSame('approved', $organizer->fresh()->organizer_status);

        $this->actingAs($organizer->fresh())
            ->get(route('organizer.dashboard'))
            ->assertOk();
    }

    public function test_admin_can_suspend_organizer(): void
    {
        $admin = User::factory()->admin()->create();
        $organizer = User::factory()->organizer()->create();

        $this->actingAs($admin)
            ->patch(route('admin.organizers.suspend', $organizer))
            ->assertRedirect(route('admin.organizers.index'));

        $this->assertNotNull($organizer->fresh()->suspended_at);

        $this->actingAs($organizer->fresh())
            ->get(route('organizer.dashboard'))
            ->assertRedirect(route('organizer.suspended'));
    }
}
