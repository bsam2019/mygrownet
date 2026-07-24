<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('phone_number')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('preferred_payment_method')->nullable();
            $table->json('payment_details')->nullable();
            $table->enum('kyc_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->json('kyc_documents')->nullable();
            $table->string('current_investment_tier')->default('Basic');
            $table->timestamps();

            $table->index(['user_id', 'kyc_status']);
            $table->index('current_investment_tier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};