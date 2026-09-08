<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->timestamp('sale_starts_at')->nullable()->after('is_seated');
            $table->timestamp('sale_ends_at')->nullable()->after('sale_starts_at');
            $table->boolean('is_active')->default(true)->after('sale_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->dropColumn(['sale_starts_at', 'sale_ends_at', 'is_active']);
        });
    }
};
