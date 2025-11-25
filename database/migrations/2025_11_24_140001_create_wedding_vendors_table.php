<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wedding_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Vendor owner
            $table->string('business_name');
            $table->string('slug')->unique();
            $table->enum('category', [
                'venue', 'photography', 'catering', 'decoration', 
                'music', 'transport', 'flowers', 'makeup', 'planning'
            ]);
            $table->string('contact_person');
            $table->string('phone');
            $table->string('email');
            $table->string('location');
            $table->text('description');
            $table->string('price_range'); // e.g., "K5,000 - K15,000"
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('review_count')->default(0);
            $table->boolean('verified')->default(false);
            $table->boolean('featured')->default(false);
            $table->json('services')->nullable(); // Array of specific services
            $table->json('portfolio_images')->nullable(); // Array of image URLs
            $table->json('availability')->nullable(); // Available dates/times
            $table->timestamps();
            
            $table->index(['category', 'verified']);
            $table->index(['location', 'category']);
            $table->index('rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_vendors');
    }
};