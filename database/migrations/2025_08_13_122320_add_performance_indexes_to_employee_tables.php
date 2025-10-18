<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add performance indexes for common employee queries and reporting
     */
    public function up(): void
    {
        // Add composite indexes for employees table
        Schema::table('employees', function (Blueprint $table) {
            // Composite index for department and status queries
            $table->index(['department_id', 'employment_status'], 'idx_employees_dept_status');
            
            // Composite index for position and status queries
            $table->index(['position_id', 'employment_status'], 'idx_employees_pos_status');
            
            // Composite index for manager and status queries
            $table->index(['manager_id', 'employment_status'], 'idx_employees_mgr_status');
            
            // Composite index for salary range queries
            $table->index(['current_salary', 'employment_status'], 'idx_employees_salary_status');
            
            // Composite index for hire date range queries
            $table->index(['hire_date', 'employment_status'], 'idx_employees_hire_status');
            
            // Composite index for termination date queries
            $table->index(['termination_date', 'employment_status'], 'idx_employees_term_status');
            
            // Full text search optimization
            $table->index(['first_name', 'last_name', 'email'], 'idx_employees_search');
            
            // Performance review eligibility queries
            $table->index(['employment_status', 'hire_date'], 'idx_employees_review_eligible');
        });

        // Add composite indexes for employee_performance table
        Schema::table('employee_performance', function (Blueprint $table) {
            // Composite index for employee performance history
            $table->index(['employee_id', 'period_end', 'status'], 'idx_performance_emp_period_status');
            
            // Composite index for reviewer queries
            $table->index(['reviewer_id', 'period_end', 'status'], 'idx_performance_reviewer_period');
            
            // Composite index for performance analytics
            $table->index(['overall_score', 'rating', 'period_end'], 'idx_performance_score_rating');
            
            // Composite index for evaluation period queries
            $table->index(['evaluation_period', 'period_start', 'period_end'], 'idx_performance_eval_period');
            
            // Index for recent performance queries
            $table->index(['period_end', 'status'], 'idx_performance_recent');
        });

        // Add composite indexes for employee_commissions table
        Schema::table('employee_commissions', function (Blueprint $table) {
            // Composite index for employee commission history
            $table->index(['employee_id', 'earned_date', 'payment_status'], 'idx_commissions_emp_earned_status');
            
            // Composite index for payroll processing
            $table->index(['payment_status', 'earned_date'], 'idx_commissions_payroll');
            
            // Composite index for commission type analytics
            $table->index(['commission_type', 'earned_date', 'amount'], 'idx_commissions_type_analytics');
            
            // Composite index for payment tracking
            $table->index(['payment_date', 'payment_status'], 'idx_commissions_payment_tracking');
            
            // Index for commission calculations
            $table->index(['earned_date', 'amount'], 'idx_commissions_calculations');
        });

        // Add composite indexes for employee_client_assignments table
        Schema::table('employee_client_assignments', function (Blueprint $table) {
            // Composite index for active assignments
            $table->index(['employee_id', 'status', 'assignment_type'], 'idx_assignments_emp_status_type');
            
            // Composite index for client assignments
            $table->index(['client_user_id', 'status', 'assignment_type'], 'idx_assignments_client_status_type');
            
            // Composite index for assignment date queries
            $table->index(['assigned_date', 'status'], 'idx_assignments_date_status');
            
            // Index for unassignment tracking
            $table->index(['unassigned_date', 'status'], 'idx_assignments_unassigned_status');
        });

        // Add composite indexes for departments table
        Schema::table('departments', function (Blueprint $table) {
            // Composite index for active department hierarchy
            $table->index(['parent_department_id', 'is_active'], 'idx_departments_parent_active');
            
            // Index for department head queries
            $table->index(['head_employee_id', 'is_active'], 'idx_departments_head_active');
        });

        // Add composite indexes for positions table
        Schema::table('positions', function (Blueprint $table) {
            // Composite index for department positions
            $table->index(['department_id', 'is_active', 'level'], 'idx_positions_dept_active_level');
            
            // Composite index for commission eligible positions
            $table->index(['base_commission_rate', 'performance_commission_rate'], 'idx_positions_commission_rates');
            
            // Composite index for salary range queries
            $table->index(['min_salary', 'max_salary', 'is_active'], 'idx_positions_salary_range');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from employees table
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('idx_employees_dept_status');
            $table->dropIndex('idx_employees_pos_status');
            $table->dropIndex('idx_employees_mgr_status');
            $table->dropIndex('idx_employees_salary_status');
            $table->dropIndex('idx_employees_hire_status');
            $table->dropIndex('idx_employees_term_status');
            $table->dropIndex('idx_employees_search');
            $table->dropIndex('idx_employees_review_eligible');
        });

        // Drop indexes from employee_performance table
        Schema::table('employee_performance', function (Blueprint $table) {
            $table->dropIndex('idx_performance_emp_period_status');
            $table->dropIndex('idx_performance_reviewer_period');
            $table->dropIndex('idx_performance_score_rating');
            $table->dropIndex('idx_performance_eval_period');
            $table->dropIndex('idx_performance_recent');
        });

        // Drop indexes from employee_commissions table
        Schema::table('employee_commissions', function (Blueprint $table) {
            $table->dropIndex('idx_commissions_emp_earned_status');
            $table->dropIndex('idx_commissions_payroll');
            $table->dropIndex('idx_commissions_type_analytics');
            $table->dropIndex('idx_commissions_payment_tracking');
            $table->dropIndex('idx_commissions_calculations');
        });

        // Drop indexes from employee_client_assignments table
        Schema::table('employee_client_assignments', function (Blueprint $table) {
            $table->dropIndex('idx_assignments_emp_status_type');
            $table->dropIndex('idx_assignments_client_status_type');
            $table->dropIndex('idx_assignments_date_status');
            $table->dropIndex('idx_assignments_unassigned_status');
        });

        // Drop indexes from departments table
        Schema::table('departments', function (Blueprint $table) {
            $table->dropIndex('idx_departments_parent_active');
            $table->dropIndex('idx_departments_head_active');
        });

        // Drop indexes from positions table
        Schema::table('positions', function (Blueprint $table) {
            $table->dropIndex('idx_positions_dept_active_level');
            $table->dropIndex('idx_positions_commission_rates');
            $table->dropIndex('idx_positions_salary_range');
        });
    }
};
