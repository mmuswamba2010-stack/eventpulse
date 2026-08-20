<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterUnsubscribeTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_inscrit_peut_se_desinscrire(): void
    {
        $subscriber = NewsletterSubscriber::create(['email' => 'fan@example.com']);

        $this->post(route('newsletter.unsubscribe.confirm', $subscriber->unsubscribe_token))
            ->assertRedirect(route('newsletter.unsubscribe', $subscriber->unsubscribe_token));

        $subscriber->refresh();
        $this->assertNotNull($subscriber->unsubscribed_at);
    }
}
