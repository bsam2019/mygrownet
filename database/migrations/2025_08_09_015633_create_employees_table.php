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
        // If the table already exists (e.g., created manually or by a prior run),
        // skip creation so this migration can be marked as completed without error.
        if (Schema::hasTable('employees')) {
            return;
        }
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 20)->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('email', 100)->unique();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('national_id', 50)->nullable()->unique();
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->enum('employment_status', ['active', 'inactive', 'terminated', 'suspended'])->default('active');
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->decimal('current_salary', 10, 2)->nullable();
            $table->json('emergency_contacts')->nullable();
            $table->json('qualifications')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('user_id', 'idx_employee_user');
            $table->index('department_id', 'idx_employee_department');
            $table->index('position_id', 'idx_employee_position');
            $table->index('manager_id', 'idx_employee_manager');
            $table->index('employment_status', 'idx_employee_status');
            $table->index('hire_date', 'idx_employee_hire_date');
            $table->index(['first_name', 'last_name'], 'idx_employee_name');
            
            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('restrict');
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('restrict');
            $table->foreign('manager_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
