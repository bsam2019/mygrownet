<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bizboost_qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('bizboost_businesses')->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // business_page, whatsapp, menu, catalog, promotion, custom
            $table->string('target_url');
            $table->string('short_code')->unique();
            $table->string('qr_image_path')->nullable();
            $table->json('style_config')->nullable(); // colors, logo, etc.
            $table->integer('scan_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['business_id', 'is_active']);
            $table->index('short_code');
        });

        // QR code scan tracking
        Schema::create('bizboost_qr_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_code_id')->constrained('bizboost_qr_codes')->onDelete('cascade');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->timestamp('scanned_at');
            
            $table->index(['qr_code_id', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_qr_scans');
        Schema::dropIfExists('bizboost_qr_codes');
    }
};
