<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_seller_followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('marketplace_sellers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['seller_id', 'user_id']);
            $table->index('seller_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_seller_followers');
    }
};
