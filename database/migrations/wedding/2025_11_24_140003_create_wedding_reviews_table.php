<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wedding_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_vendor_id')->constrained()->onDelete('cascade');
            $table->foreignId('wedding_booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Reviewer
            $table->integer('rating'); // 1-5 stars
            $table->text('review');
            $table->json('images')->nullable(); // Review images
            $table->boolean('verified')->default(false); // Verified purchase
            $table->timestamp('service_date')->nullable();
            $table->timestamps();
            
            $table->unique(['wedding_booking_id', 'user_id']);
            $table->index(['wedding_vendor_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_reviews');
    }
};