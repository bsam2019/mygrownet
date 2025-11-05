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
        Schema::create('position_kpis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('position_id');
            $table->string('kpi_name');
            $table->text('kpi_description')->nullable();
            $table->decimal('target_value', 10, 2)->nullable();
            $table->string('measurement_unit', 50)->nullable();
            $table->enum('measurement_frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'annual'])->default('monthly');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade');
            $table->index(['position_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('position_kpis');
    }
};
