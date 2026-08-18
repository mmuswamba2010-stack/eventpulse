<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EventPulseDemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $organizer1 = User::updateOrCreate(
            ['email' => 'orga@eventpulse.test'],
            [
                'name' => 'Sara Organisatrice',
                'password' => $password,
                'role' => 'organizer',
                'phone' => '0611111111',
                'mobile_money_provider' => 'orange_money',
                'bank_account_holder' => 'Sara Organisatrice',
                'bank_name' => 'Attijariwafa Bank',
                'bank_account_number' => 'MA6411111111111111111111111',
                'email_verified_at' => now(),
            ]
        );

        $organizer2 = User::updateOrCreate(
            ['email' => 'studio@eventpulse.test'],
            [
                'name' => 'Studio Pulse Events',
                'password' => $password,
                'role' => 'organizer',
                'phone' => '0622222222',
                'mobile_money_provider' => 'mpesa',
                'bank_account_holder' => 'Studio Pulse Events',
                'bank_name' => 'BMCE Bank',
                'bank_account_number' => 'MA6422222222222222222222222',
                'email_verified_at' => now(),
            ]
        );

        $participant1 = User::updateOrCreate(
            ['email' => 'participant@eventpulse.test'],
            [
                'name' => 'Amine Participant',
                'password' => $password,
                'role' => 'participant',
                'phone' => '0633333333',
                'email_verified_at' => now(),
            ]
        );

        $participant2 = User::updateOrCreate(
            ['email' => 'fan@eventpulse.test'],
            [
                'name' => 'Lina Fan',
                'password' => $password,
                'role' => 'participant',
                'phone' => '0644444444',
                'email_verified_at' => now(),
            ]
        );

        $festival = $this->upsertEvent($organizer1, [
            'title' => 'Festival des Lumières',
            'description' => 'Soirée immersive de mapping vidéo, DJ sets et installations lumineuses.',
            'location' => 'Casablanca — Parc de la Ligue Arabe',
            'category' => 'music',
            'event_date' => now()->addDays(12)->setTime(20, 0),
            'placement_mode' => Event::PLACEMENT_STANDING,
            'payment_method' => 'mobile_money',
        ], [
            ['name' => 'Standard', 'price' => 180, 'quantity' => 150],
            ['name' => 'VIP', 'price' => 350, 'quantity' => 50],
        ]);

        $conference = $this->upsertEvent($organizer1, [
            'title' => 'Conférence Tech & Startups',
            'description' => 'Keynotes, panels et networking : IA, produit et growth.',
            'location' => 'Rabat — Technopark',
            'category' => 'tech',
            'event_date' => now()->addDays(20)->setTime(9, 30),
            'placement_mode' => Event::PLACEMENT_SEATED,
            'payment_method' => 'card',
        ], [
            ['name' => 'Standard', 'price' => 0, 'quantity' => 80],
            ['name' => 'VIP', 'price' => 150, 'quantity' => 40],
        ]);

        $electro = $this->upsertEvent($organizer2, [
            'title' => 'Concert Live — Nuit Électro',
            'description' => 'Nuit électro avec trois headliners, light show et food trucks.',
            'location' => 'Marrakech — Théâtre Royal',
            'category' => 'music',
            'event_date' => now()->addDays(8)->setTime(22, 0),
            'placement_mode' => Event::PLACEMENT_STANDING,
            'payment_method' => 'mobile_money',
        ], [
            ['name' => 'Debout', 'price' => 250, 'quantity' => 300],
            ['name' => 'VVIP', 'price' => 500, 'quantity' => 50],
        ]);

        $photo = $this->upsertEvent($organizer2, [
            'title' => 'Atelier Photo Street Style',
            'description' => 'Masterclass photo urbaine : composition et lumière naturelle.',
            'location' => 'Tanger — Médina',
            'category' => 'art',
            'event_date' => now()->addDays(30)->setTime(15, 0),
            'placement_mode' => Event::PLACEMENT_STANDING,
            'payment_method' => 'card',
        ], [
            ['name' => 'Pass journée', 'price' => 95, 'quantity' => 40],
        ]);

        $comedy = $this->upsertEvent($organizer1, [
            'title' => 'Stand-up Comedy Night',
            'description' => 'Soirée humour avec 5 humoristes émergents.',
            'location' => 'Casablanca — Théâtre de Verdure',
            'category' => 'conference',
            'event_date' => now()->addDays(5)->setTime(19, 30),
            'placement_mode' => Event::PLACEMENT_SEATED,
            'payment_method' => 'mobile_money',
        ], [
            ['name' => 'Orchestre', 'price' => 120, 'quantity' => 50],
            ['name' => 'Balcon', 'price' => 80, 'quantity' => 30],
        ]);

        $yoga = $this->upsertEvent($organizer2, [
            'title' => 'Yoga Sunrise Session',
            'description' => 'Séance de yoga en plein air au lever du soleil.',
            'location' => 'Agadir — Plage Anza',
            'category' => 'sport',
            'event_date' => now()->addDays(15)->setTime(6, 30),
            'placement_mode' => Event::PLACEMENT_STANDING,
            'payment_method' => 'card',
        ], [
            ['name' => 'Standard', 'price' => 75, 'quantity' => 50],
        ]);

        $this->upsertEvent($organizer1, [
            'title' => 'Afterwork Networking (brouillon)',
            'description' => 'Brouillon : payez les frais de publication (20 $) pour publier.',
            'location' => 'Casablanca — Twin Center',
            'category' => 'conference',
            'event_date' => now()->addDays(40)->setTime(18, 30),
            'placement_mode' => Event::PLACEMENT_STANDING,
            'status' => 'draft',
            'is_paid' => false,
            'payment_method' => null,
            'paid_at' => null,
        ], [
            ['name' => 'Standard', 'price' => 50, 'quantity' => 60],
        ], paid: false);

        // Billets démo : debout + assis.
        $this->seedTickets($festival, $participant1, 'VIP', 1, seated: false);
        $this->seedTickets($festival, $participant1, 'Standard', 1, seated: false);
        $this->seedTickets($festival, $participant2, 'Standard', 1, seated: false);
        $this->seedTickets($comedy, $participant1, 'Orchestre', 1, seated: true);
        $this->seedTickets($electro, $participant2, 'Debout', 2, seated: false);
        $this->seedTickets($yoga, $participant1, 'Standard', 1, seated: false);
        $this->seedTickets($conference, $participant2, 'VIP', 1, seated: true);
        $this->seedTickets($photo, $participant2, 'Pass journée', 1, seated: false);

        $this->command?->newLine();
        $this->command?->info('=== Comptes démo Event Pulse ===');
        $this->command?->table(
            ['Rôle', 'Email', 'Mot de passe'],
            [
                ['Organisateur', 'orga@eventpulse.test', 'password'],
                ['Organisateur', 'studio@eventpulse.test', 'password'],
                ['Participant', 'participant@eventpulse.test', 'password'],
                ['Participant', 'fan@eventpulse.test', 'password'],
            ]
        );
        $this->command?->info('6 événements publiés (debout + assis, multi-tarifs) + 1 brouillon.');
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, array{name: string, price: float|int, quantity: int}>  $types
     */
    private function upsertEvent(User $organizer, array $attributes, array $types, bool $paid = true): Event
    {
        $slug = Str::slug($attributes['title']);
        $capacity = collect($types)->sum('quantity');
        $minPrice = collect($types)->min('price');
        $isSeated = ($attributes['placement_mode'] ?? Event::PLACEMENT_STANDING) === Event::PLACEMENT_SEATED;

        $event = Event::updateOrCreate(
            ['slug' => $slug],
            array_merge([
                'user_id' => $organizer->id,
                'slug' => $slug,
                'capacity' => $capacity,
                'price' => $minPrice,
                'status' => $paid ? 'published' : 'draft',
                'is_paid' => $paid,
                'publication_fee' => Event::publicationFee(),
                'paid_at' => $paid ? now()->subDays(rand(1, 8)) : null,
                'accepted_payment_methods' => ['mobile_money', 'card', 'cash'],
            ], $attributes)
        );

        // Réinitialise billets + types (seeder démo idempotent).
        Ticket::where('event_id', $event->id)->delete();
        $event->ticketTypes()->delete();
        foreach ($types as $type) {
            $event->ticketTypes()->create([
                'name' => $type['name'],
                'price' => $type['price'],
                'quantity' => $type['quantity'],
                'is_seated' => $isSeated,
            ]);
        }

        return $event->fresh('ticketTypes');
    }

    private function seedTickets(Event $event, User $user, string $typeName, int $count, bool $seated): void
    {
        /** @var TicketType|null $type */
        $type = $event->ticketTypes->firstWhere('name', $typeName);
        if (! $type) {
            return;
        }

        $existing = Ticket::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->where('ticket_type_id', $type->id)
            ->count();

        for ($i = $existing; $i < $count; $i++) {
            Ticket::create([
                'event_id' => $event->id,
                'ticket_type_id' => $type->id,
                'user_id' => $user->id,
                'ticket_code' => Ticket::generateUniqueCode(),
                'ticket_number' => Ticket::generateUniqueTicketNumber(),
                'seat_number' => $seated ? $type->nextSeatLabel() : null,
                'status' => 'valid',
            ]);
        }
    }
}
