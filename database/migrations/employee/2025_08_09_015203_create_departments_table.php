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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('head_employee_id')->nullable();
            $table->unsignedBigInteger('parent_department_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('head_employee_id', 'idx_department_head');
            $table->index('parent_department_id', 'idx_department_parent');
            $table->index('is_active', 'idx_department_active');
            
            // Foreign key constraints (will be added after employees table is created)
            // $table->foreign('head_employee_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('parent_department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
