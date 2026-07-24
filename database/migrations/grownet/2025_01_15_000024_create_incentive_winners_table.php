<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incentive_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incentive_program_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('position');
            $table->string('reward_type');
            $table->decimal('reward_amount', 10, 2)->default(0);
            $table->datetime('awarded_at');
            $table->enum('status', ['pending', 'awarded', 'failed'])->default('pending');
            $table->timestamps();

            $table->unique(['incentive_program_id', 'user_id']);
            $table->index(['position']);
            $table->index(['awarded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incentive_winners');
    }
};