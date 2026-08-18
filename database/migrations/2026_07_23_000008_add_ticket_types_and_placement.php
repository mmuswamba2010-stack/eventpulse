<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('placement_mode')->default('standing')->after('status');
        });

        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price', 8, 2)->default(0);
            $table->unsignedInteger('quantity');
            $table->boolean('is_seated')->default(false);
            $table->timestamps();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('ticket_type_id')->nullable()->after('event_id')->constrained()->nullOnDelete();
            $table->string('seat_number')->nullable()->after('ticket_code');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ticket_type_id');
            $table->dropColumn('seat_number');
        });

        Schema::dropIfExists('ticket_types');

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('placement_mode');
        });
    }
};
