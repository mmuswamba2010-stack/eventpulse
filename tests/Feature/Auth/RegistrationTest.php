<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee(__('Register hub title'), false);

        $this->get('/register/participant')->assertOk();
        $this->get('/register/organizer')->assertOk();
    }

    public function test_new_users_can_register_as_participant(): void
    {
        $response = $this->post('/register/participant', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('events.index', absolute: false));
        $response->assertSessionHas('success', __('Account created welcome participant', ['name' => 'Test']));
    }

    public function test_new_organizers_are_redirected_to_their_dashboard(): void
    {
        $response = $this->post('/register/organizer', [
            'name' => 'Test Organizer',
            'email' => 'organizer@example.com',
            'password' => 'password123',
            'phone' => '+243812345678',
            'accept_organizer_terms' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('organizer.dashboard', absolute: false));
        $response->assertSessionHas('success', __('Account created welcome organizer', ['name' => 'Test']));
    }
}
