<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventPublicationPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['eventpulse.require_publication_payment' => true]);
    }

    private function organizer(): User
    {
        return User::factory()->create(['role' => 'organizer']);
    }

    /**
     * @return array<string, mixed>
     */
    private function paymentProfilePayload(): array
    {
        return [
            'accepted_payment_methods' => ['mobile_money', 'card', 'cash'],
            'organizer_phone' => '0612345678',
            'organizer_mobile_provider' => 'orange_money',
            'bank_account_holder' => 'Organisateur Test',
            'bank_name' => 'Attijariwafa Bank',
            'bank_account_number' => 'MA6412345678901234567890123',
        ];
    }

    private function draftEvent(User $organizer, array $attributes = []): Event
    {
        return Event::create(array_merge([
            'user_id' => $organizer->id,
            'title' => 'Festival Non Payé',
            'slug' => 'festival-non-paye-'.uniqid(),
            'description' => 'Un événement en attente de paiement.',
            'location' => 'Marrakech',
            'category' => 'music',
            'event_date' => now()->addDays(15),
            'capacity' => 100,
            'price' => 150.00,
            'status' => 'draft',
            'is_paid' => false,
            'publication_fee' => Event::publicationFee(),
        ], $attributes));
    }

    public function test_un_nouvel_evenement_est_cree_en_brouillon_et_redirige_vers_le_paiement(): void
    {
        $organizer = $this->organizer();

        $response = $this->actingAs($organizer)->post('/organizer/events', array_merge([
            'title' => 'Concert Impayé',
            'description' => 'Description.',
            'location' => 'Tanger',
            'category' => 'music',
            'event_date' => now()->addMonth()->format('Y-m-d H:i'),
            'placement_mode' => 'standing',
            'ticket_types' => [
                ['name' => 'Standard', 'price' => 120, 'quantity' => 80],
            ],
        ], $this->paymentProfilePayload()));

        $event = Event::where('title', 'Concert Impayé')->firstOrFail();

        $response->assertRedirect(route('organizer.events.pay', $event));
        $this->assertSame('draft', $event->status);
        $this->assertFalse($event->is_paid);
        $this->assertEquals(Event::publicationFee(), $event->publication_fee);
    }

    public function test_un_evenement_non_paye_n_apparait_pas_dans_le_catalogue_public(): void
    {
        $event = $this->draftEvent($this->organizer());

        $this->get('/')->assertOk()->assertDontSee($event->title);
        $this->get('/events/grid')->assertOk()->assertDontSee($event->title);
        $this->get('/events/'.$event->slug)->assertNotFound();
    }

    public function test_la_page_de_paiement_affiche_le_recapitulatif_et_le_montant(): void
    {
        $organizer = $this->organizer();
        $event = $this->draftEvent($organizer);

        $this->actingAs($organizer)
            ->get(route('organizer.events.pay', $event))
            ->assertOk()
            ->assertSee($event->title)
            ->assertSee('Mobile Money')
            ->assertSee('Envoyez le paiement à')
            ->assertSee('0699999999')
            ->assertSee(number_format($event->publication_fee, 0, ',', ' '))
            ->assertSee('FC')
            ->assertDontSee('Carte bancaire');
    }

    public function test_un_organisateur_ne_peut_pas_payer_l_evenement_d_un_autre(): void
    {
        $event = $this->draftEvent($this->organizer());
        $other = $this->organizer();

        $this->actingAs($other)
            ->get(route('organizer.events.pay', $event))
            ->assertForbidden();

        $this->actingAs($other)
            ->post(route('organizer.events.pay.process', $event), [
                'payment_method' => 'mobile_money',
                'mobile_provider' => 'orange_money',
                'phone_number' => '0612345678',
            ])
            ->assertForbidden();
    }

    public function test_le_paiement_par_mobile_money_publie_l_evenement(): void
    {
        $organizer = $this->organizer();
        $event = $this->draftEvent($organizer);

        $this->actingAs($organizer)
            ->post(route('organizer.events.pay.process', $event), [
                'payment_method' => 'mobile_money',
                'mobile_provider' => 'orange_money',
                'phone_number' => '0612345678',
            ])
            ->assertRedirect(route('organizer.events.index'));

        $event->refresh();
        $this->assertTrue($event->is_paid);
        $this->assertSame('published', $event->status);

        $this->get('/')->assertOk()->assertSee($event->title);
    }

    public function test_le_paiement_echoue_sans_les_champs_requis(): void
    {
        $organizer = $this->organizer();
        $event = $this->draftEvent($organizer);

        $this->actingAs($organizer)
            ->post(route('organizer.events.pay.process', $event), [
                'payment_method' => 'mobile_money',
            ])
            ->assertSessionHasErrors(['mobile_provider', 'phone_number']);

        $this->assertFalse($event->fresh()->is_paid);
    }

    public function test_un_evenement_deja_paye_redirige_hors_de_la_page_de_paiement(): void
    {
        $organizer = $this->organizer();
        $event = $this->draftEvent($organizer, ['is_paid' => true, 'status' => 'published']);

        $this->actingAs($organizer)
            ->get(route('organizer.events.pay', $event))
            ->assertRedirect(route('organizer.events.index'));
    }
}
