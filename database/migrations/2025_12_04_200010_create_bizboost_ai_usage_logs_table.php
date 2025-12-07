<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bizboost_ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('content_type'); // caption, ad, description, idea, whatsapp
            $table->string('model')->default('gpt-4o-mini');
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->integer('credits_used')->default(1);
            $table->json('request_params')->nullable();
            $table->text('prompt')->nullable();
            $table->text('response')->nullable();
            $table->boolean('was_successful')->default(true);
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['business_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_ai_usage_logs');
    }
};
