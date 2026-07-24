<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_seller_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('marketplace_sellers')->onDelete('cascade');
            $table->string('filename');
            $table->string('original_name');
            $table->string('path');
            $table->string('disk')->default('public');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->json('variants')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_seller_media');
    }
};
