<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPasswordCommand extends Command
{
    protected $signature = 'eventpulse:reset-password {email : Adresse e-mail du compte} {--password= : Nouveau mot de passe (généré si omis)}';

    protected $description = 'Réinitialiser le mot de passe d\'un utilisateur Event Pulse';

    public function handle(): int
    {
        $email = Str::lower(trim($this->argument('email')));
        $password = $this->option('password') ?: 'Ep-'.Str::random(10).'!';

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("Aucun compte trouvé pour {$email}.");

            return self::FAILURE;
        }

        $user->update(['password' => Hash::make($password)]);

        $this->info("Mot de passe réinitialisé pour {$user->name} ({$user->email}) — rôle : {$user->role}");
        $this->line("Nouveau mot de passe temporaire : {$password}");

        return self::SUCCESS;
    }
}
