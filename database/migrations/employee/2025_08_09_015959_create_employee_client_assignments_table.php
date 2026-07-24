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
        Schema::create('employee_client_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('client_user_id'); // References users table for client
            $table->enum('assignment_type', ['primary', 'secondary', 'support']);
            $table->date('assigned_date');
            $table->date('unassigned_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'transferred'])->default('active');
            $table->text('assignment_notes')->nullable();
            $table->unsignedBigInteger('assigned_by');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('employee_id', 'idx_assignment_employee');
            $table->index('client_user_id', 'idx_assignment_client');
            $table->index('assignment_type', 'idx_assignment_type');
            $table->index('status', 'idx_assignment_status');
            $table->index('assigned_date', 'idx_assignment_date');
            $table->unique(['employee_id', 'client_user_id', 'assignment_type'], 'idx_unique_assignment');
            
            // Foreign key constraints
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('client_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('employees')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_client_assignments');
    }
};
