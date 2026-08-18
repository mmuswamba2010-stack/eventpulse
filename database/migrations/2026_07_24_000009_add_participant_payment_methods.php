<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->json('accepted_payment_methods')->nullable()->after('placement_mode');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('seat_number');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('accepted_payment_methods');
        });
    }
};
