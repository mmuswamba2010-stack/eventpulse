<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\OrganizerTerms;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizerTermsTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizer_terms_page_is_public(): void
    {
        $this->get(route('legal.organizer-terms'))
            ->assertOk()
            ->assertSee(__('Organizer terms section data title'), false);
    }

    public function test_organizer_registration_requires_terms_acceptance(): void
    {
        $response = $this->from('/register/organizer')->post('/register/organizer', [
            'name' => 'Organizer Test',
            'email' => 'organizer-terms@example.com',
            'password' => 'password123',
            'phone' => '+243812345678',
            'role' => 'organizer',
        ]);

        $response->assertRedirect('/register/organizer');
        $response->assertSessionHasErrors('accept_organizer_terms');
        $this->assertGuest();
    }

    public function test_organizer_without_terms_is_redirected_to_accept_page(): void
    {
        $organizer = User::factory()->create([
            'role' => 'organizer',
            'organizer_status' => 'approved',
            'organizer_terms_accepted_at' => null,
            'organizer_terms_version' => null,
        ]);

        $this->actingAs($organizer)
            ->get(route('organizer.dashboard'))
            ->assertRedirect(route('organizer.terms.accept'));
    }

    public function test_organizer_can_accept_terms_and_access_dashboard(): void
    {
        $organizer = User::factory()->create([
            'role' => 'organizer',
            'organizer_status' => 'approved',
            'organizer_terms_accepted_at' => null,
            'organizer_terms_version' => null,
        ]);

        $this->actingAs($organizer)
            ->post(route('organizer.terms.store'), [
                'accept_organizer_terms' => '1',
            ])
            ->assertRedirect(route('organizer.dashboard'))
            ->assertSessionHas('success');

        $organizer->refresh();

        $this->assertNotNull($organizer->organizer_terms_accepted_at);
        $this->assertSame(OrganizerTerms::version(), $organizer->organizer_terms_version);
    }
}
