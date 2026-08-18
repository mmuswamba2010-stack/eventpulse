<?php

namespace App\Support;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Str;

final class VitrineEvents
{
    public const ORGANIZER_EMAIL = 'contact@eventpulse.cd';

    /**
     * @return list<array<string, mixed>>
     */
    public static function definitions(): array
    {
        return [
            [
                'title' => 'Forum Tech & Innovation — Kinshasa',
                'description' => "Rencontre gratuite pour fondateurs, développeurs et créateurs d'entreprise.\n\nAu programme : keynotes sur l'IA et le Mobile Money, tables rondes avec des mentors locaux, et sessions de networking dans le quartier de la Gombe.\n\nPlaces limitées — réservez votre entrée en ligne.",
                'location' => 'Kinshasa — Gombe, Centre Kongo',
                'category' => 'tech',
                'days_from_now' => 10,
                'hour' => 9,
                'minute' => 0,
                'placement_mode' => Event::PLACEMENT_STANDING,
                'accepted_payment_methods' => [],
                'types' => [
                    ['name' => 'Entrée Gratuite', 'price' => 0, 'quantity' => 200],
                ],
            ],
            [
                'title' => 'Conférence Entrepreneuriat Jeunes — Kinshasa',
                'description' => "Journée gratuite dédiée aux jeunes entrepreneurs de Kinshasa et du Kongo.\n\nInterventions sur le financement, le marketing digital et la gestion d'équipe. Remise de attestations de participation sur place.\n\nInscription obligatoire via Event Pulse.",
                'location' => 'Kinshasa — Lingwala, Institut Français',
                'category' => 'conference',
                'days_from_now' => 18,
                'hour' => 14,
                'minute' => 0,
                'placement_mode' => Event::PLACEMENT_STANDING,
                'accepted_payment_methods' => [],
                'types' => [
                    ['name' => 'Accès Général', 'price' => 0, 'quantity' => 150],
                ],
            ],
            [
                'title' => 'Concert Live — Nuit Rumba Kin',
                'description' => "Soirée payante avec les meilleurs artistes rumba congolaise et afrobeat.\n\nScène live, sound system professionnel et espace VIP. Paiement par Mobile Money ou espèces à l'entrée après réservation en ligne.\n\nNe manquez pas l'événement musical de la saison à Kinshasa !",
                'location' => 'Kinshasa — Bandalungwa, Stade Tata Raphaël',
                'category' => 'music',
                'days_from_now' => 25,
                'hour' => 20,
                'minute' => 0,
                'placement_mode' => Event::PLACEMENT_STANDING,
                'accepted_payment_methods' => ['mobile_money', 'cash'],
                'payment_method' => 'mobile_money',
                'types' => [
                    ['name' => 'Standard', 'price' => 15000, 'quantity' => 500],
                    ['name' => 'VIP', 'price' => 35000, 'quantity' => 80],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    public static function slug(array $definition): string
    {
        return Str::slug($definition['title']);
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    public static function scheduledAt(array $definition, ?Carbon $reference = null): Carbon
    {
        $reference ??= now();

        return $reference->copy()
            ->addDays((int) $definition['days_from_now'])
            ->setTime((int) $definition['hour'], (int) $definition['minute']);
    }

    /**
     * @return list<string>
     */
    public static function slugs(): array
    {
        return array_map(fn (array $definition): string => self::slug($definition), self::definitions());
    }
}
