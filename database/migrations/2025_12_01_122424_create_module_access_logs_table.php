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
        Schema::create('module_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('module_id', 50);
            $table->string('action', 50); // 'accessed', 'feature_used', 'error'
            $table->string('route')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('accessed_at');
            
            $table->foreign('module_id')->references('id')->on('modules');
            $table->index(['user_id', 'module_id']);
            $table->index('accessed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_access_logs');
    }
};
