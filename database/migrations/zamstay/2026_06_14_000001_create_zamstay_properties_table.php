<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zamstay_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('location');
            $table->decimal('price_per_night', 10, 2);
            $table->enum('status', ['available', 'booked', 'unavailable'])->default('available');
            $table->json('images')->nullable();
            $table->integer('max_guests')->default(1);
            $table->integer('bedrooms')->default(1);
            $table->integer('bathrooms')->default(1);
            $table->json('amenities')->nullable();
            $table->string('property_type')->default('hotel');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zamstay_properties');
    }
};
