<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('organizer_terms_accepted_at')->nullable()->after('organizer_moderation_note');
            $table->string('organizer_terms_version', 32)->nullable()->after('organizer_terms_accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['organizer_terms_accepted_at', 'organizer_terms_version']);
        });
    }
};
