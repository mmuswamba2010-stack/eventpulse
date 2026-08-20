<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MailTestCommand extends Command
{
    protected $signature = 'eventpulse:mail-test {email : Adresse de destination}';

    protected $description = 'Envoie un e-mail de test (local SMTP ou production sendmail)';

    public function handle(): int
    {
        $email = $this->argument('email');

        try {
            Mail::raw('Ceci est un e-mail de test Event Pulse. Si vous le recevez, l\'envoi fonctionne.', function ($message) use ($email) {
                $message->to($email)->subject('Test Event Pulse — envoi e-mail');
            });
        } catch (\Throwable $e) {
            $this->error('Échec : '.$e->getMessage());
            $this->line('');
            $this->line('Local (Gmail) : vérifiez MAIL_PASSWORD (mot de passe d\'application Google).');
            $this->line('Production : vérifiez MAIL_MAILER=sendmail et MAIL_SENDMAIL_PATH.');

            return self::FAILURE;
        }

        $this->info("E-mail de test envoyé à {$email}.");

        return self::SUCCESS;
    }
}
