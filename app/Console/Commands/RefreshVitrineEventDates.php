<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\User;
use App\Support\VitrineEvents;
use Illuminate\Console\Command;

class RefreshVitrineEventDates extends Command
{
    protected $signature = 'eventpulse:refresh-vitrine-dates
                            {--force : Met à jour les dates même si elles sont encore dans le futur}';

    protected $description = 'Recalcule les dates des événements vitrine pour qu’ils restent visibles au catalogue';

    public function handle(): int
    {
        $organizer = User::query()
            ->where('email', VitrineEvents::ORGANIZER_EMAIL)
            ->first();

        if (! $organizer) {
            $this->warn('Aucun compte vitrine ('.VitrineEvents::ORGANIZER_EMAIL.'). Lancez EventSeeder d’abord.');

            return self::SUCCESS;
        }

        $updated = 0;

        foreach (VitrineEvents::definitions() as $definition) {
            $slug = VitrineEvents::slug($definition);
            $event = Event::query()
                ->where('user_id', $organizer->id)
                ->where('slug', $slug)
                ->first();

            if (! $event) {
                $this->line("Ignoré : {$slug} (introuvable).");

                continue;
            }

            $nextDate = VitrineEvents::scheduledAt($definition);
            $shouldUpdate = $this->option('force') || $event->event_date->isPast();

            if (! $shouldUpdate) {
                $this->line("Conservé : {$event->title} ({$event->event_date->format('d/m/Y H:i')}).");

                continue;
            }

            $event->update(['event_date' => $nextDate]);
            $updated++;

            $this->info("Mis à jour : {$event->title} → {$nextDate->format('d/m/Y H:i')}.");
        }

        $this->comment("{$updated} événement(s) vitrine mis à jour.");

        return self::SUCCESS;
    }
}
