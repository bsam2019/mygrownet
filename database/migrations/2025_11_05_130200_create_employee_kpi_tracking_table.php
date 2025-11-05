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
        Schema::create('employee_kpi_tracking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('position_kpi_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('actual_value', 10, 2)->nullable();
            $table->decimal('target_value', 10, 2)->nullable();
            $table->decimal('achievement_percentage', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();
            
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('position_kpi_id')->references('id')->on('position_kpis')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['employee_id', 'period_start', 'period_end']);
            $table->index('position_kpi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_kpi_tracking');
    }
};
