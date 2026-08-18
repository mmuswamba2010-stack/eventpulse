<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Aligne les brouillons non payés existants sur le nouveau montant.
        DB::table('events')->where('is_paid', false)->update(['publication_fee' => 20.00]);

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // SQLite ne supporte pas ALTER COLUMN ... SET DEFAULT ; le
            // contrôleur fixe de toute façon explicitement la valeur.
            return;
        }

        DB::statement('ALTER TABLE events ALTER COLUMN publication_fee SET DEFAULT 20.00');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE events ALTER COLUMN publication_fee SET DEFAULT 50.00');
    }
};
