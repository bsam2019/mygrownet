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
        Schema::create('storage_folders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->uuid('parent_id')->nullable();
            $table->string('name');
            $table->text('path_cache')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('storage_folders')->onDelete('cascade');
            $table->index(['user_id', 'parent_id']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_folders');
    }
};
