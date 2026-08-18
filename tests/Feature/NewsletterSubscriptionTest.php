<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_visiteur_peut_s_inscrire_a_la_newsletter(): void
    {
        $this->from('/')
            ->post(route('newsletter.subscribe'), ['email' => 'fan@example.com'])
            ->assertRedirect(route('events.index').'#about')
            ->assertSessionHas('newsletter_success');

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'fan@example.com',
        ]);
    }

    public function test_une_inscription_en_double_affiche_un_message_informatif(): void
    {
        NewsletterSubscriber::create(['email' => 'fan@example.com']);

        $this->from('/')
            ->post(route('newsletter.subscribe'), ['email' => 'fan@example.com'])
            ->assertRedirect(route('events.index').'#about')
            ->assertSessionHas('newsletter_info');

        $this->assertSame(1, NewsletterSubscriber::count());
    }

    public function test_l_email_est_obligatoire_et_valide(): void
    {
        $this->post(route('newsletter.subscribe'), [])
            ->assertSessionHasErrors(['email']);

        $this->post(route('newsletter.subscribe'), ['email' => 'pas-un-email'])
            ->assertSessionHasErrors(['email']);
    }
}
