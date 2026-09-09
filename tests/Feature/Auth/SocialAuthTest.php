<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Support\SocialAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'google-client-id',
            'services.google.client_secret' => 'google-client-secret',
            'services.facebook.client_id' => 'facebook-client-id',
            'services.facebook.client_secret' => 'facebook-client-secret',
        ]);
    }

    public function test_les_boutons_sociaux_s_affichent_quand_les_fournisseurs_sont_configures(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee(__('Continue with Google'), false)
            ->assertSee(__('Continue with Facebook'), false);

        $this->get('/register/participant')
            ->assertOk()
            ->assertSee(__('Continue with Google'), false);

        $this->get('/register/organizer')
            ->assertOk()
            ->assertSee(__('Continue with Facebook'), false);
    }

    public function test_la_connexion_google_cree_un_compte_participant(): void
    {
        $this->mockSocialUser('google', 'google-123', 'Alice Social', 'alice@example.com');

        $response = $this->withSession(['social_auth_context' => 'participant-register'])
            ->get('/auth/google/callback');

        $this->assertAuthenticated();
        $response->assertRedirect(route('events.index', absolute: false));

        $user = User::where('email', 'alice@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('participant', $user->role);
        $this->assertSame('google-123', $user->google_id);
        $this->assertNull($user->password);
    }

    public function test_la_connexion_google_organisateur_redirige_vers_la_finalisation(): void
    {
        $this->mockSocialUser('google', 'google-org-1', 'Bob Org', 'bob@example.com');

        $response = $this->withSession(['social_auth_context' => 'organizer-register'])
            ->get('/auth/google/callback');

        $this->assertAuthenticated();
        $response->assertRedirect(route('register.organizer.complete', absolute: false));

        $user = User::where('email', 'bob@example.com')->first();
        $this->assertSame('organizer', $user->role);
        $this->assertNull($user->phone);
    }

    public function test_la_finalisation_organisateur_enregistre_telephone_et_conditions(): void
    {
        $user = User::factory()->create([
            'role' => 'organizer',
            'organizer_status' => 'approved',
            'phone' => null,
            'organizer_terms_accepted_at' => null,
        ]);

        $this->actingAs($user)
            ->post('/register/organizer/complete', [
                'phone' => '+243812345678',
                'accept_organizer_terms' => '1',
            ])
            ->assertRedirect(route('organizer.dashboard', absolute: false));

        $user->refresh();
        $this->assertSame('+243812345678', $user->phone);
        $this->assertNotNull($user->organizer_terms_accepted_at);
    }

    public function test_un_fournisseur_non_configure_est_refuse(): void
    {
        config(['services.google.client_id' => null]);

        $this->assertFalse(SocialAuth::isConfigured('google'));

        $this->from('/login')
            ->get('/auth/google/redirect?context=login')
            ->assertRedirect('/login')
            ->assertSessionHas('error');
    }

    private function mockSocialUser(string $provider, string $id, string $name, string $email): void
    {
        $socialUser = Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getId')->andReturn($id);
        $socialUser->shouldReceive('getName')->andReturn($name);
        $socialUser->shouldReceive('getNickname')->andReturn('');
        $socialUser->shouldReceive('getEmail')->andReturn($email);

        $driver = Mockery::mock();
        $driver->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with($provider)->andReturn($driver);
    }
}
