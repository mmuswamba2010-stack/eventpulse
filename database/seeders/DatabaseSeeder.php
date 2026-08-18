<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Événements vitrine — local et production (catalogue jamais vide au lancement).
        $this->call(EventSeeder::class);

        // Comptes démo (password) — développement uniquement.
        if (! app()->environment('production')) {
            $this->call(EventPulseDemoSeeder::class);
        }
    }
}
