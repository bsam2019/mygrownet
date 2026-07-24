<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quick_invoice_user_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('tier_id');
            $table->timestamp('starts_at');
            $table->timestamp('expires_at')->nullable();
            $table->integer('documents_used')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index(['expires_at', 'is_active']);
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('tier_id')->references('id')->on('quick_invoice_subscription_tiers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quick_invoice_user_subscriptions');
    }
};