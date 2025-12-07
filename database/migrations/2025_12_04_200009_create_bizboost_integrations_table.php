<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bizboost_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
            $table->string('provider'); // facebook, instagram, whatsapp
            $table->string('provider_user_id')->nullable();
            $table->string('provider_page_id')->nullable();
            $table->string('provider_page_name')->nullable();
            $table->text('access_token')->nullable(); // encrypted
            $table->text('refresh_token')->nullable(); // encrypted
            $table->timestamp('token_expires_at')->nullable();
            $table->json('scopes')->nullable();
            $table->json('meta')->nullable();
            $table->string('status')->default('active'); // active, expired, revoked
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            $table->unique(['business_id', 'provider', 'provider_page_id']);
            $table->index(['business_id', 'provider', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_integrations');
    }
};
