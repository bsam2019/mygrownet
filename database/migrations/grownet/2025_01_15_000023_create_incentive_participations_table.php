<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incentive_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incentive_program_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->datetime('participation_date');
            $table->decimal('qualification_score', 10, 2)->default(0);
            $table->decimal('reward_earned', 10, 2)->default(0);
            $table->enum('status', ['active', 'completed', 'disqualified'])->default('active');
            $table->timestamps();

            $table->unique(['incentive_program_id', 'user_id']);
            $table->index(['qualification_score']);
            $table->index(['participation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incentive_participations');
    }
};