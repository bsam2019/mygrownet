<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growbuilder_site_payment_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->cascadeOnDelete();
            $table->string('gateway'); // pawapay, moneyunify, mtn_momo, airtel_money, zamtel_kwacha
            $table->text('credentials'); // Encrypted JSON
            $table->boolean('is_active')->default(true);
            $table->boolean('test_mode')->default(false);
            $table->string('webhook_secret')->nullable();
            $table->json('settings')->nullable(); // Additional gateway-specific settings
            $table->timestamps();

            $table->index(['site_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growbuilder_site_payment_configs');
    }
};
