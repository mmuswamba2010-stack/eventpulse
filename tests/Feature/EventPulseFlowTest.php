<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventPulseFlowTest extends TestCase
{
    use RefreshDatabase;

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

    private function participant(): User
    {
        return User::factory()->create(['role' => 'participant']);
    }

    private function event(User $organizer, array $attributes = [], array $typeOverrides = []): Event
    {
        $event = Event::create(array_merge([
            'user_id' => $organizer->id,
            'title' => 'Concert Test',
            'slug' => 'concert-test-'.uniqid(),
            'description' => 'Description du concert de test.',
            'location' => 'Casablanca',
            'category' => 'music',
            'event_date' => now()->addDays(10),
            'capacity' => 50,
            'price' => 100.00,
            'status' => 'published',
            'is_paid' => true,
            'placement_mode' => Event::PLACEMENT_STANDING,
        ], $attributes));

        $event->ticketTypes()->create(array_merge([
            'name' => 'Standard',
            'price' => $event->price,
            'quantity' => $event->capacity,
            'is_seated' => $event->isSeatedPlacement(),
        ], $typeOverrides));

        return $event->fresh('ticketTypes');
    }

    public function test_le_catalogue_public_affiche_les_evenements(): void
    {
        $event = $this->event($this->organizer());

        // Shell + skeleton + fallback noscript.
        $this->get('/')
            ->assertOk()
            ->assertSee('animate-pulse', false)
            ->assertSee($event->title);

        // Grille async (source réelle du catalogue côté JS).
        $this->get('/events/grid')
            ->assertOk()
            ->assertSee($event->title);
    }

    public function test_la_page_detail_est_accessible_par_slug(): void
    {
        $event = $this->event($this->organizer());

        $this->get('/events/'.$event->slug)
            ->assertOk()
            ->assertSee($event->title)
            ->assertSee($event->location);
    }

    public function test_un_participant_peut_reserver_des_billets(): void
    {
        $event = $this->event($this->organizer());
        $participant = $this->participant();
        $typeId = $event->ticketTypes->first()->id;

        $this->actingAs($participant)
            ->post("/events/{$event->id}/book", [
                'ticket_type_id' => $typeId,
                'quantity' => 2,
                'payment_method' => 'cash',
            ])
            ->assertRedirect('/my-tickets');

        $this->assertSame(2, $event->tickets()->where('user_id', $participant->id)->count());
        $this->assertSame(48, $event->fresh()->remainingSeats());
        $ticket = $event->tickets()->first();
        $this->assertNull($ticket->seat_number);
        $this->assertSame('cash', $ticket->payment_method);
        $this->assertMatchesRegularExpression('/^EP-[A-Z2-9]{4}-[A-Z2-9]{4}$/', $ticket->ticket_number);
        $this->assertNotSame($ticket->ticket_code, $ticket->ticket_number);
    }

    public function test_la_reservation_est_refusee_si_complet(): void
    {
        $event = $this->event($this->organizer(), ['capacity' => 1], ['quantity' => 1]);
        $participant = $this->participant();
        $type = $event->ticketTypes->first();

        Ticket::create([
            'event_id' => $event->id,
            'ticket_type_id' => $type->id,
            'user_id' => $this->participant()->id,
            'ticket_code' => Ticket::generateUniqueCode(),
            'ticket_number' => Ticket::generateUniqueTicketNumber(),
        ]);

        $this->actingAs($participant)
            ->post("/events/{$event->id}/book", [
                'ticket_type_id' => $type->id,
                'quantity' => 1,
                'payment_method' => 'cash',
            ])
            ->assertSessionHas('error');

        $this->assertSame(0, $event->tickets()->where('user_id', $participant->id)->count());
    }

    public function test_un_billet_assis_recoit_un_numero_de_siege(): void
    {
        $event = $this->event($this->organizer(), [
            'placement_mode' => Event::PLACEMENT_SEATED,
            'title' => 'Gala Assis',
            'slug' => 'gala-assis-'.uniqid(),
        ], [
            'name' => 'VIP',
            'is_seated' => true,
        ]);
        $participant = $this->participant();
        $typeId = $event->ticketTypes->first()->id;

        $this->actingAs($participant)
            ->post("/events/{$event->id}/book", [
                'ticket_type_id' => $typeId,
                'quantity' => 1,
                'payment_method' => 'card',
                'card_name' => 'Amine Test',
                'card_number' => '4242424242424242',
                'card_expiry' => '12/29',
                'card_cvc' => '123',
            ])
            ->assertRedirect('/my-tickets');

        $ticket = $event->tickets()->first();
        $this->assertNotNull($ticket->seat_number);
        $this->assertStringContainsString('Rangée', $ticket->seat_number);
        $this->assertStringContainsString('ZONE VIP', $ticket->accessLabel());
    }

    public function test_le_billet_affiche_son_qr_code(): void
    {
        $event = $this->event($this->organizer());
        $participant = $this->participant();
        $ticket = Ticket::create([
            'event_id' => $event->id,
            'user_id' => $participant->id,
            'ticket_code' => Ticket::generateUniqueCode(),
            'ticket_number' => Ticket::generateUniqueTicketNumber(),
        ]);

        $this->actingAs($participant)
            ->get("/my-tickets/{$ticket->id}")
            ->assertOk()
            ->assertSee('data:image/svg+xml;base64,', false)
            ->assertSee($ticket->formatted_number)
            ->assertDontSee($ticket->ticket_code, false);
    }

    public function test_un_participant_ne_peut_pas_voir_le_billet_d_un_autre(): void
    {
        $event = $this->event($this->organizer());
        $ticket = Ticket::create([
            'event_id' => $event->id,
            'user_id' => $this->participant()->id,
            'ticket_code' => Ticket::generateUniqueCode(),
            'ticket_number' => Ticket::generateUniqueTicketNumber(),
        ]);

        $this->actingAs($this->participant())
            ->get("/my-tickets/{$ticket->id}")
            ->assertForbidden();
    }

    public function test_le_billet_est_telechargeable_en_pdf(): void
    {
        $event = $this->event($this->organizer());
        $participant = $this->participant();
        $ticket = Ticket::create([
            'event_id' => $event->id,
            'user_id' => $participant->id,
            'ticket_code' => Ticket::generateUniqueCode(),
            'ticket_number' => Ticket::generateUniqueTicketNumber(),
        ]);

        $response = $this->actingAs($participant)->get("/my-tickets/{$ticket->id}/download");

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_un_participant_ne_peut_pas_acceder_a_l_espace_organisateur(): void
    {
        $this->actingAs($this->participant())
            ->get('/organizer/dashboard')
            ->assertForbidden();
    }

    public function test_le_scan_valide_un_billet_puis_le_signale_comme_deja_utilise(): void
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);
        $ticket = Ticket::create([
            'event_id' => $event->id,
            'user_id' => $this->participant()->id,
            'ticket_code' => Ticket::generateUniqueCode(),
            'ticket_number' => Ticket::generateUniqueTicketNumber(),
        ]);

        // Premier scan : succès.
        $this->actingAs($organizer)
            ->postJson('/organizer/scan/validate', ['ticket_code' => $ticket->ticket_code])
            ->assertOk()
            ->assertJson(['result' => 'success']);

        $this->assertSame('used', $ticket->fresh()->status);
        $this->assertNotNull($ticket->fresh()->scanned_at);

        // Deuxième scan : alerte anti-fraude.
        $this->actingAs($organizer)
            ->postJson('/organizer/scan/validate', ['ticket_code' => $ticket->ticket_code])
            ->assertStatus(409)
            ->assertJson(['result' => 'already_used']);

        $this->assertDatabaseHas('scans', ['ticket_id' => $ticket->id, 'status' => 'success']);
        $this->assertDatabaseHas('scans', ['ticket_id' => $ticket->id, 'status' => 'already_used']);
    }

    public function test_le_scan_d_un_code_inconnu_est_invalide(): void
    {
        $this->actingAs($this->organizer())
            ->postJson('/organizer/scan/validate', ['ticket_code' => 'CODE-INEXISTANT'])
            ->assertStatus(404)
            ->assertJson(['result' => 'invalid']);
    }

    public function test_un_organisateur_ne_peut_pas_valider_le_billet_d_un_autre_organisateur(): void
    {
        $event = $this->event($this->organizer());
        $ticket = Ticket::create([
            'event_id' => $event->id,
            'user_id' => $this->participant()->id,
            'ticket_code' => Ticket::generateUniqueCode(),
            'ticket_number' => Ticket::generateUniqueTicketNumber(),
        ]);

        $this->actingAs($this->organizer())
            ->postJson('/organizer/scan/validate', ['ticket_code' => $ticket->ticket_code])
            ->assertStatus(404)
            ->assertJson(['result' => 'invalid']);

        $this->assertSame('valid', $ticket->fresh()->status);
    }

    public function test_un_organisateur_peut_creer_un_evenement(): void
    {
        $organizer = $this->organizer();

        $this->actingAs($organizer)
            ->post('/organizer/events', array_merge([
                'title' => 'Mon Nouveau Festival',
                'description' => 'Un super festival.',
                'location' => 'Rabat',
                'category' => 'music',
                'event_date' => now()->addMonth()->format('Y-m-d H:i'),
                'placement_mode' => 'standing',
                'ticket_types' => [
                    ['name' => 'Standard', 'price' => 250, 'quantity' => 200],
                    ['name' => 'VIP', 'price' => 500, 'quantity' => 50],
                ],
            ], $this->paymentProfilePayload()))
            ->assertRedirect(route('organizer.events.index'));

        $event = Event::where('title', 'Mon Nouveau Festival')->firstOrFail();

        $this->assertDatabaseHas('events', [
            'title' => 'Mon Nouveau Festival',
            'slug' => 'mon-nouveau-festival',
            'user_id' => $organizer->id,
            'status' => 'published',
            'is_paid' => false,
            'capacity' => 250,
            'price' => 250,
        ]);
        $this->assertSame(2, $event->ticketTypes()->count());
    }

    public function test_les_passes_vides_recoivent_des_valeurs_par_defaut(): void
    {
        $organizer = $this->organizer();

        $this->actingAs($organizer)
            ->post('/organizer/events', array_merge([
                'title' => 'Meetup Gratuit',
                'description' => 'Rencontre ouverte.',
                'location' => 'Kinshasa',
                'category' => 'conference',
                'event_date' => now()->addWeek()->format('Y-m-d H:i'),
                'placement_mode' => 'standing',
                'ticket_types' => [
                    ['name' => '', 'price' => '', 'quantity' => 100],
                ],
            ], $this->paymentProfilePayload()))
            ->assertRedirect(route('organizer.events.index'));

        $type = Event::where('title', 'Meetup Gratuit')->firstOrFail()->ticketTypes()->first();
        $this->assertSame('Entrée Gratuite', $type->name);
        $this->assertSame(0.0, (float) $type->price);

        $this->actingAs($organizer)
            ->post('/organizer/events', array_merge([
                'title' => 'Concert Payant Express',
                'description' => 'Concert rapide.',
                'location' => 'Lubumbashi',
                'category' => 'music',
                'event_date' => now()->addWeeks(2)->format('Y-m-d H:i'),
                'placement_mode' => 'standing',
                'ticket_types' => [
                    ['name' => '', 'price' => 15000, 'quantity' => 80],
                ],
            ], $this->paymentProfilePayload()))
            ->assertRedirect(route('organizer.events.index'));

        $paidType = Event::where('title', 'Concert Payant Express')->firstOrFail()->ticketTypes()->first();
        $this->assertSame('Accès Général', $paidType->name);
        $this->assertSame(15000.0, (float) $paidType->price);
    }

    public function test_un_evenement_gratuit_ne_requiert_pas_les_moyens_de_paiement(): void
    {
        $organizer = $this->organizer();

        $this->actingAs($organizer)
            ->post('/organizer/events', [
                'title' => 'Meetup Gratuit Sans Paiement',
                'description' => 'Rencontre ouverte.',
                'location' => 'Kinshasa',
                'category' => 'conference',
                'event_date' => now()->addWeek()->format('Y-m-d H:i'),
                'placement_mode' => 'standing',
                'ticket_types' => [
                    ['name' => '', 'price' => 0, 'quantity' => 50],
                ],
            ])
            ->assertRedirect(route('organizer.events.index'));

        $event = Event::where('title', 'Meetup Gratuit Sans Paiement')->firstOrFail();
        $this->assertSame([], $event->accepted_payment_methods);
        $this->assertTrue($event->isFreeEvent());
    }

    public function test_un_participant_peut_reserver_un_billet_gratuit_sans_paiement(): void
    {
        $event = $this->event($this->organizer(), [
            'title' => 'Atelier Gratuit',
            'slug' => 'atelier-gratuit-'.uniqid(),
            'accepted_payment_methods' => [],
        ], [
            'name' => 'Entrée Gratuite',
            'price' => 0,
        ]);

        $participant = $this->participant();
        $typeId = $event->ticketTypes->first()->id;

        $this->actingAs($participant)
            ->post("/events/{$event->id}/book", [
                'ticket_type_id' => $typeId,
                'quantity' => 1,
            ])
            ->assertRedirect('/my-tickets');

        $ticket = $event->tickets()->first();
        $this->assertNull($ticket->payment_method);
    }

    public function test_un_evenement_payant_exige_au_moins_un_moyen_de_paiement(): void
    {
        $organizer = $this->organizer();

        $this->actingAs($organizer)
            ->post('/organizer/events', [
                'title' => 'Concert Sans Paiement Config',
                'description' => 'Test validation.',
                'location' => 'Kinshasa',
                'category' => 'music',
                'event_date' => now()->addWeek()->format('Y-m-d H:i'),
                'placement_mode' => 'standing',
                'ticket_types' => [
                    ['name' => 'Standard', 'price' => 5000, 'quantity' => 100],
                ],
                'accepted_payment_methods' => [],
            ])
            ->assertSessionHasErrors(['accepted_payment_methods']);
    }
}
