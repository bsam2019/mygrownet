<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('network_change_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('old_referrer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('new_referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('target_referrer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('performed_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_spillover')->default(false);
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable(); // Store additional data like old/new paths, levels, etc.
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index('performed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('network_change_history');
    }
};
