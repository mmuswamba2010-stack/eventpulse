<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('purpose'); // publication_fee, ticket_purchase
            $table->nullableMorphs('payable');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider'); // mpesa, orange_money, airtel_money
            $table->string('phone')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('CDF');
            $table->string('status')->default('pending'); // pending, succeeded, failed, expired
            $table->string('external_id')->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('webhook_received_at')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('organizer_status')->nullable()->after('role');
            $table->timestamp('organizer_reviewed_at')->nullable()->after('organizer_status');
            $table->text('organizer_moderation_note')->nullable()->after('organizer_reviewed_at');
            $table->timestamp('suspended_at')->nullable()->after('organizer_moderation_note');
            $table->softDeletes();
        });

        DB::table('users')
            ->where('role', 'organizer')
            ->whereNull('organizer_status')
            ->update(['organizer_status' => 'approved']);

        Schema::table('events', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('payment_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->softDeletes();
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tickets MODIFY status ENUM('pending','valid','used','cancelled') NOT NULL DEFAULT 'valid'");
        } else {
            Schema::table('tickets', function (Blueprint $table) {
                $table->string('status')->default('valid')->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_id');
            $table->dropSoftDeletes();
        });

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            Schema::table('tickets', function (Blueprint $table) {
                $table->enum('status', ['valid', 'used', 'cancelled'])->default('valid')->change();
            });
        }

        Schema::table('events', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'organizer_status',
                'organizer_reviewed_at',
                'organizer_moderation_note',
                'suspended_at',
            ]);
        });

        Schema::dropIfExists('payments');
    }
};
