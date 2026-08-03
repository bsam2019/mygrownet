<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstream_creator_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('growstream_creator_profiles')->cascadeOnDelete();
            $table->decimal('price_monthly', 15, 2)->default(0);
            $table->string('currency', 3)->default('ZMW');
            $table->string('status', 20)->default('active');
            $table->timestamp('started_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('provider_reference')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'creator_id']);
            $table->index(['creator_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstream_creator_subscriptions');
    }
};
