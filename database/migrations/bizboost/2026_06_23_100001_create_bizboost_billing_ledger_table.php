<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bizboost_billing_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('service_type', 32);
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->string('recipient_identifier', 100)->nullable();
            $table->decimal('gross_amount_charged', 14, 4);
            $table->decimal('net_vendor_cost', 14, 4);
            $table->decimal('pure_platform_profit', 14, 4);
            $table->string('currency', 3)->default('ZMW');
            $table->string('vendor', 64)->nullable();
            $table->string('delivery_status', 32)->default('pending');
            $table->string('reference', 100)->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index('service_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_billing_ledger');
    }
};
