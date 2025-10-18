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
        Schema::create('matrix_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('sponsor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('level')->default(1); // Matrix level (1, 2, or 3)
            $table->integer('position')->default(1); // Position within the level (1-3 for level 1, 1-9 for level 2, etc.)
            $table->foreignId('left_child_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('middle_child_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('right_child_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamp('placed_at')->useCurrent();
            $table->timestamps();

            // Indexes for efficient queries
            $table->index(['user_id', 'level']);
            $table->index(['sponsor_id', 'level']);
            $table->index(['is_active', 'level']);
            $table->unique(['user_id', 'level']); // Each user can only have one position per level
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matrix_positions');
    }
};
