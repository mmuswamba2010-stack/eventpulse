<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'participant',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('events.index', absolute: false));
        $response->assertSessionHas('success', 'Bienvenue, Test ! Votre compte est prêt — découvrez les événements et réservez vos places.');

        $this->followRedirects($response)
            ->assertOk()
            ->assertSee('Bienvenue, Test !', false);
    }

    public function test_new_organizers_are_redirected_to_their_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Organizer',
            'email' => 'organizer@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'organizer',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('organizer.dashboard', absolute: false));
        $response->assertSessionHas('success', 'Bienvenue, Test ! Votre espace organisateur est prêt — publiez votre premier événement.');

        $this->followRedirects($response)
            ->assertOk()
            ->assertSee('Bienvenue, Test !', false);
    }
}
