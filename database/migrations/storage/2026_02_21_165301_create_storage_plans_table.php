<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('storage_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Lite, Basic, Growth, Pro
            $table->string('slug')->unique();
            $table->unsignedBigInteger('quota_bytes'); // 2GB, 20GB, 100GB, 500GB
            $table->unsignedBigInteger('max_file_size_bytes');
            $table->json('allowed_mime_types')->nullable();
            $table->boolean('allow_sharing')->default(false);
            $table->boolean('allow_public_profile_files')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->timestamps();
            
            $table->index('slug');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_plans');
    }
};
