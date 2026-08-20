<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('organizer', 'participant', 'admin') NOT NULL DEFAULT 'participant'");
        }

        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->string('unsubscribe_token', 64)->nullable()->unique()->after('email');
            $table->timestamp('unsubscribed_at')->nullable()->after('unsubscribe_token');
        });

        foreach (\Illuminate\Support\Facades\DB::table('newsletter_subscribers')->whereNull('unsubscribe_token')->cursor() as $row) {
            \Illuminate\Support\Facades\DB::table('newsletter_subscribers')
                ->where('id', $row->id)
                ->update(['unsubscribe_token' => \Illuminate\Support\Str::random(64)]);
        }
    }

    public function down(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->dropColumn(['unsubscribe_token', 'unsubscribed_at']);
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('organizer', 'participant') NOT NULL DEFAULT 'participant'");
        }
    }
};
