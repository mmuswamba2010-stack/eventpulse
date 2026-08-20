<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileMoneyWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'eventpulse.payment_simulation' => false,
            'eventpulse.webhook_secret' => 'test-webhook-secret',
            'eventpulse.require_publication_payment' => true,
        ]);
    }

    public function test_webhook_confirms_publication_payment(): void
    {
        $organizer = User::factory()->organizer()->create();
        $event = Event::create([
            'user_id' => $organizer->id,
            'title' => 'Webhook Test Event',
            'slug' => 'webhook-test-'.uniqid(),
            'description' => 'Test',
            'location' => 'Kinshasa',
            'category' => 'music',
            'event_date' => now()->addMonth(),
            'capacity' => 100,
            'price' => 100,
            'status' => 'draft',
            'is_paid' => false,
            'publication_fee' => 25000,
        ]);

        $this->actingAs($organizer)->post(route('organizer.events.pay.process', $event), [
            'payment_method' => 'mobile_money',
            'mobile_provider' => 'orange_money',
            'phone_number' => '0812345678',
        ])->assertRedirect();

        $payment = Payment::firstOrFail();
        $this->assertTrue($payment->isPending());
        $this->assertFalse($event->fresh()->is_paid);

        $payload = [
            'reference' => $payment->reference,
            'external_id' => 'MM-123456',
            'status' => 'succeeded',
            'amount' => (float) $payment->amount,
        ];

        $body = json_encode($payload);
        $signature = hash_hmac('sha256', $body, 'test-webhook-secret');

        $this->call(
            'POST',
            route('webhooks.mobile-money'),
            server: [
                'HTTP_X-EventPulse-Signature' => $signature,
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: $body,
        )->assertOk();

        $this->assertTrue($payment->fresh()->isSucceeded());
        $this->assertTrue($event->fresh()->is_paid);
        $this->assertSame('published', $event->fresh()->status);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $payload = [
            'reference' => 'EP-TEST-1234',
            'external_id' => 'MM-1',
            'status' => 'succeeded',
        ];

        $this->postJson(route('webhooks.mobile-money'), $payload, [
            'X-EventPulse-Signature' => 'invalid',
        ])->assertUnauthorized();
    }
}
