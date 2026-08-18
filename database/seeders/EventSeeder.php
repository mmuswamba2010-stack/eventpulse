<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use App\Support\VitrineEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EventSeeder extends Seeder
{
    /** Mot de passe du compte vitrine contact@eventpulse.cd (local uniquement si .env absent). */
    public const ORGANIZER_PASSWORD = 'Ksh@EventPulse2026!';

    public function run(): void
    {
        $organizer = User::firstOrNew(['email' => VitrineEvents::ORGANIZER_EMAIL]);

        if (! $organizer->exists) {
            $plainPassword = env('EVENTSEEDER_ORGANIZER_PASSWORD');

            if (! $plainPassword && ! app()->environment('production')) {
                $plainPassword = self::ORGANIZER_PASSWORD;
            }

            if (! $plainPassword) {
                $this->command?->error('EventSeeder : définissez EVENTSEEDER_ORGANIZER_PASSWORD dans .env pour créer le compte vitrine.');

                return;
            }

            $organizer->password = Hash::make($plainPassword);
        }

        $organizer->fill([
            'name' => 'Event Pulse Kinshasa',
            'role' => 'organizer',
            'phone' => '0812345678',
            'mobile_money_provider' => 'mpesa',
            'bank_account_holder' => 'Event Pulse Kinshasa',
            'bank_name' => 'Rawbank',
            'bank_account_number' => '00012345678901',
            'email_verified_at' => $organizer->email_verified_at ?? now(),
        ]);
        $organizer->save();

        foreach (VitrineEvents::definitions() as $data) {
            $this->seedEvent($organizer, $data);
        }

        $this->command?->info('EventSeeder : '.VitrineEvents::ORGANIZER_EMAIL.' + 3 événements vitrines Kinshasa.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function seedEvent(User $organizer, array $data): Event
    {
        $types = $data['types'];
        $eventDate = VitrineEvents::scheduledAt($data);

        unset($data['types'], $data['days_from_now'], $data['hour'], $data['minute']);

        $slug = VitrineEvents::slug(['title' => $data['title']]);
        $capacity = (int) collect($types)->sum('quantity');
        $minPrice = (float) collect($types)->min('price');
        $isFree = Event::ticketTypesAreFree($types);

        $event = Event::updateOrCreate(
            ['slug' => $slug],
            array_merge([
                'user_id' => $organizer->id,
                'slug' => $slug,
                'capacity' => $capacity,
                'price' => $minPrice,
                'status' => 'published',
                'is_paid' => false,
                'publication_fee' => Event::publicationFee(),
                'paid_at' => null,
                'placement_mode' => Event::PLACEMENT_STANDING,
                'accepted_payment_methods' => $isFree ? [] : ($data['accepted_payment_methods'] ?? ['mobile_money', 'cash']),
                'event_date' => $eventDate,
            ], $data)
        );

        $event->ticketTypes()->delete();

        foreach ($types as $type) {
            $event->ticketTypes()->create([
                'name' => $type['name'],
                'price' => $type['price'],
                'quantity' => (int) $type['quantity'],
                'is_seated' => false,
            ]);
        }

        return $event->fresh('ticketTypes');
    }
}
