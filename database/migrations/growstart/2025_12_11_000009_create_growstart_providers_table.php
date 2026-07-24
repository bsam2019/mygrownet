<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstart_partner_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['accountant', 'designer', 'pacra_agent', 'marketing', 'legal', 'supplier', 'consultant', 'other']);
            $table->text('description')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('website')->nullable();
            $table->string('province');
            $table->string('city');
            $table->foreignId('country_id')->constrained('growstart_countries');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->decimal('rating', 2, 1)->default(0);
            $table->integer('review_count')->default(0);
            $table->timestamps();

            $table->index(['country_id', 'category', 'province']);
            $table->index(['is_featured', 'is_verified']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstart_partner_providers');
    }
};
