<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growbuilder_site_payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->cascadeOnDelete();
            $table->foreignId('payment_config_id')->constrained('growbuilder_site_payment_configs')->cascadeOnDelete();
            $table->string('transaction_reference')->unique();
            $table->string('external_reference')->nullable()->index();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('ZMW');
            $table->string('phone_number');
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->text('description');
            $table->string('status'); // pending, processing, completed, failed, cancelled, refunded, expired
            $table->json('metadata')->nullable();
            $table->json('raw_response')->nullable();
            $table->string('refund_reference')->nullable();
            $table->decimal('refund_amount', 15, 2)->nullable();
            $table->text('refund_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'status']);
            $table->index(['site_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growbuilder_site_payment_transactions');
    }
};
