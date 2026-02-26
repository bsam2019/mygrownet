<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_shares', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('file_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('share_token', 64)->unique();
            $table->string('password')->nullable(); // Optional password protection
            $table->timestamp('expires_at')->nullable();
            $table->integer('max_downloads')->nullable();
            $table->integer('download_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_preview')->default(true);
            $table->timestamps();
            
            $table->foreign('file_id')->references('id')->on('storage_files')->onDelete('cascade');
            $table->index(['share_token', 'is_active']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_shares');
    }
};
