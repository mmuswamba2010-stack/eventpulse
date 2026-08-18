<?php

use App\Models\Ticket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('ticket_number', 20)->nullable()->unique()->after('ticket_code');
        });

        Ticket::query()
            ->whereNull('ticket_number')
            ->orderBy('id')
            ->each(function (Ticket $ticket) {
                $ticket->forceFill([
                    'ticket_number' => Ticket::generateUniqueTicketNumber(),
                ])->saveQuietly();
            });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropUnique(['ticket_number']);
            $table->dropColumn('ticket_number');
        });
    }
};
