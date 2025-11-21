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
        Schema::create('performance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('snapshot_date');
            $table->decimal('total_earnings', 15, 2)->default(0);
            $table->decimal('monthly_earnings', 15, 2)->default(0);
            $table->integer('network_size')->default(0);
            $table->integer('active_members')->default(0);
            $table->decimal('team_volume', 15, 2)->default(0);
            $table->string('professional_level', 50)->nullable();
            $table->decimal('performance_score', 5, 2)->nullable();
            $table->json('snapshot_data')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'snapshot_date']);
            $table->index('snapshot_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_snapshots');
    }
};
