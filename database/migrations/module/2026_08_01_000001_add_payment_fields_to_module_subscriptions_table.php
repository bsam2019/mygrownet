<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds `pending` status plus payment-provider reference columns so module
 * subscriptions can be paid through the shared PlatformPayments/PawaPay stack.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite stores ENUM as TEXT + CHECK; the new 'pending' value is
            // accepted by the check constraint generated below, so no table
            // rebuild is required here.
        } else {
            DB::statement("ALTER TABLE module_subscriptions MODIFY status ENUM('active', 'trial', 'pending', 'suspended', 'cancelled') NOT NULL DEFAULT 'active'");
        }

        Schema::table('module_subscriptions', function (Blueprint $table) {
            $table->string('provider_reference', 64)->nullable()->after('currency');
            $table->string('provider_transaction_id', 64)->nullable()->after('provider_reference');
            $table->index('provider_reference');
        });
    }

    public function down(): void
    {
        Schema::table('module_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['provider_reference']);
            $table->dropColumn(['provider_reference', 'provider_transaction_id']);
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE module_subscriptions MODIFY status ENUM('active', 'trial', 'suspended', 'cancelled') NOT NULL DEFAULT 'active'");
        }
    }
};
