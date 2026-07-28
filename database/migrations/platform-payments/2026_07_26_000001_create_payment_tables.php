<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_transactions')) {
            return;
        }

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('payment_method');
            $table->string('status')->default('initiated');
            $table->string('provider_transaction_id')->nullable()->unique();
            $table->string('provider_reference')->nullable();
            $table->string('provider');
            $table->decimal('fee', 15, 2)->nullable();
            $table->decimal('settled_amount', 15, 2)->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->text('failure_reason')->nullable();
            $table->integer('attempt_count')->default(0);
            $table->timestamps();
        });

        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('payment_transactions')->onDelete('cascade');
            $table->integer('attempt_number');
            $table->string('status')->default('pending');
            $table->json('provider_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('attempted_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('payment_settlements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->string('provider');
            $table->string('provider_settlement_id');
            $table->decimal('expected_amount', 15, 2);
            $table->decimal('actual_amount', 15, 2);
            $table->decimal('fee_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('pending');
            $table->timestamp('settlement_date')->nullable();
            $table->timestamp('reconciled_at')->nullable();
            $table->text('discrepancy_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settlements');
        Schema::dropIfExists('payment_attempts');
        Schema::dropIfExists('payment_transactions');
    }
};
