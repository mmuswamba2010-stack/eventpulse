<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ApprovePendingOrganizersCommand extends Command
{
    protected $signature = 'eventpulse:approve-pending-organizers {--email= : Approuver un e-mail précis}';

    protected $description = 'Approuve les organisateurs en attente de modération.';

    public function handle(): int
    {
        $query = User::query()
            ->where('role', 'organizer')
            ->where('organizer_status', 'pending');

        if ($email = $this->option('email')) {
            $query->where('email', $email);
        }

        $organizers = $query->get();

        if ($organizers->isEmpty()) {
            $this->info('Aucun organisateur en attente.');

            return self::SUCCESS;
        }

        foreach ($organizers as $organizer) {
            $organizer->update([
                'organizer_status' => 'approved',
                'organizer_reviewed_at' => now(),
            ]);

            $this->line("Approuvé : {$organizer->email}");
        }

        $this->info("{$organizers->count()} organisateur(s) approuvé(s).");

        return self::SUCCESS;
    }
}
