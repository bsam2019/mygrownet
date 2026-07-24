<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('starter_kit_content_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', ['training', 'ebook', 'video', 'tool', 'library']);
            $table->integer('unlock_day')->default(0); // 0 = immediate, 1-30 = days after purchase
            $table->string('file_path')->nullable();
            $table->string('file_type')->nullable(); // pdf, video, zip, etc.
            $table->integer('file_size')->nullable(); // in KB
            $table->string('thumbnail')->nullable();
            $table->integer('estimated_value')->default(0); // in Kwacha
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('starter_kit_content_items');
    }
};
