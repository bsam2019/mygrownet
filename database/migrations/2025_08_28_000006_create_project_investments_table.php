<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('community_project_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'refunded'])->default('pending');
            $table->date('invested_at');
            $table->decimal('expected_return', 10, 2)->nullable();
            $table->decimal('actual_return', 10, 2)->default(0);
            $table->boolean('auto_reinvest')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'community_project_id']);
            $table->index(['community_project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_investments');
    }
};