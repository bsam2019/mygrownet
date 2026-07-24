<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bizboost_client_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('balance', 14, 4)->default(0);
            $table->decimal('locked_balance', 14, 4)->default(0);
            $table->string('currency', 3)->default('ZMW');
            $table->timestamps();
            $table->unique('user_id');
        });

        Schema::create('bizboost_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('bizboost_client_wallets')->cascadeOnDelete();
            $table->string('type', 32);
            $table->decimal('amount', 14, 4);
            $table->decimal('balance_before', 14, 4);
            $table->decimal('balance_after', 14, 4);
            $table->string('currency', 3)->default('ZMW');
            $table->string('reference', 100)->nullable()->index();
            $table->string('description')->nullable();
            $table->morphs('payable');
            $table->string('status', 32)->default('completed');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['wallet_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_wallet_transactions');
        Schema::dropIfExists('bizboost_client_wallets');
    }
};
