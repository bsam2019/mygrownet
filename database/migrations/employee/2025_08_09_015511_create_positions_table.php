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
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('department_id');
            $table->decimal('min_salary', 10, 2)->nullable();
            $table->decimal('max_salary', 10, 2)->nullable();
            $table->decimal('base_commission_rate', 5, 4)->default(0.0000);
            $table->decimal('performance_commission_rate', 5, 4)->default(0.0000);
            $table->json('permissions')->nullable();
            $table->integer('level')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('department_id', 'idx_position_department');
            $table->index('level', 'idx_position_level');
            $table->index('is_active', 'idx_position_active');
            $table->index(['department_id', 'title'], 'idx_position_dept_title');
            
            // Foreign key constraints
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
