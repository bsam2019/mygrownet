<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the 'pending' status to module_subscriptions on SQLite.
 *
 * MySQL got 'pending' via 2026_08_01_000001 (ALTER ... MODIFY ENUM). SQLite
 * enforces ENUMs with a CHECK constraint baked into the CREATE TABLE, so a
 * no-op branch there left the constraint as ('active','trial','suspended',
 * 'cancelled') — inserting a pending subscription failed with
 * "CHECK constraint failed: status". Rebuild the table with the constraint
 * including 'pending'. No-op on MySQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        if (! Schema::hasTable('module_subscriptions')) {
            return;
        }

        $existing = DB::table('module_subscriptions')->get();

        Schema::drop('module_subscriptions');

        Schema::create('module_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('module_id', 50);
            $table->string('subscription_tier', 50);

            $table->enum('status', ['active', 'trial', 'pending', 'suspended', 'cancelled'])->default('active');

            $table->timestamp('started_at');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->boolean('auto_renew')->default(true);
            $table->enum('billing_cycle', ['monthly', 'annual'])->default('monthly');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('ZMW');

            $table->integer('user_limit')->nullable();
            $table->integer('storage_limit_mb')->nullable();

            $table->string('provider_reference', 64)->nullable()->after('currency');
            $table->string('provider_transaction_id', 64)->nullable()->after('provider_reference');

            $table->timestamps();

            $table->foreign('module_id')->references('id')->on('modules');
            $table->unique(['user_id', 'module_id']);
            $table->index('status');
            $table->index('expires_at');
            $table->index('provider_reference');
        });

        foreach ($existing as $row) {
            DB::table('module_subscriptions')->insert((array) $row);
        }
    }

    public function down(): void
    {
        // Non-destructive: the table keeps whatever constraint it has.
    }
};
