<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PromoteAdminCommand extends Command
{
    protected $signature = 'eventpulse:promote-admin {email : Adresse e-mail du compte à promouvoir}';

    protected $description = 'Promouvoir un utilisateur au rôle administrateur Event Pulse';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("Aucun compte trouvé pour {$email}.");

            return self::FAILURE;
        }

        if ($user->isAdmin()) {
            $this->info("{$email} est déjà administrateur.");

            return self::SUCCESS;
        }

        $user->update(['role' => 'admin']);

        $this->info("{$user->name} ({$email}) est maintenant administrateur Event Pulse.");

        return self::SUCCESS;
    }
}
