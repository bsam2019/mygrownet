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
        Schema::create('physical_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reward_type'); // smartphone, motorbike, vehicle, luxury_vehicle, investment_property
            $table->string('level_achieved'); // professional, senior, manager, director, executive, ambassador
            $table->enum('status', ['pending', 'approved', 'processing', 'delivered', 'cancelled'])->default('pending');
            $table->text('description')->nullable();
            $table->timestamp('earned_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Additional data like model, color, specifications
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('reward_type');
            $table->index('status');
            $table->index('level_achieved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physical_rewards');
    }
};
