<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raffle_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incentive_program_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('entry_count')->default(1);
            $table->decimal('qualification_score', 10, 2)->default(0);
            $table->json('entry_source')->nullable();
            $table->decimal('bonus_multiplier', 3, 2)->default(1.00);
            $table->boolean('is_winner')->default(false);
            $table->integer('winning_position')->nullable();
            $table->json('reward_details')->nullable();
            $table->timestamps();

            $table->unique(['incentive_program_id', 'user_id']);
            $table->index(['entry_count']);
            $table->index(['is_winner']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raffle_entries');
    }
};