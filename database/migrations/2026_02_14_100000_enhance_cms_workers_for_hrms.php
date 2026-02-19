<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_workers', function (Blueprint $table) {
            // Personal Information Enhancement
            if (!Schema::hasColumn('cms_workers', 'first_name')) {
                $table->string('first_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('cms_workers', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('cms_workers', 'middle_name')) {
                $table->string('middle_name')->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('cms_workers', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('id_number');
            }
            if (!Schema::hasColumn('cms_workers', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('cms_workers', 'nationality')) {
                $table->string('nationality')->default('Zambian')->after('gender');
            }
            if (!Schema::hasColumn('cms_workers', 'address')) {
                $table->text('address')->nullable()->after('nationality');
            }
            if (!Schema::hasColumn('cms_workers', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            if (!Schema::hasColumn('cms_workers', 'province')) {
                $table->string('province')->nullable()->after('city');
            }
            
            // Emergency Contact
            if (!Schema::hasColumn('cms_workers', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('province');
            }
            if (!Schema::hasColumn('cms_workers', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            }
            if (!Schema::hasColumn('cms_workers', 'emergency_contact_relationship')) {
                $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_phone');
            }
            
            // Employment Details
            if (!Schema::hasColumn('cms_workers', 'job_title')) {
                $table->string('job_title')->nullable()->after('worker_type');
            }
            if (!Schema::hasColumn('cms_workers', 'department_id')) {
                $table->foreignId('department_id')->nullable()->constrained('cms_departments')->nullOnDelete()->after('job_title');
            }
            if (!Schema::hasColumn('cms_workers', 'hire_date')) {
                $table->date('hire_date')->nullable()->after('department_id');
            }
            if (!Schema::hasColumn('cms_workers', 'employment_type')) {
                $table->enum('employment_type', ['casual', 'contract', 'permanent', 'part_time', 'intern'])->default('casual')->after('hire_date');
            }
            if (!Schema::hasColumn('cms_workers', 'contract_start_date')) {
                $table->date('contract_start_date')->nullable()->after('employment_type');
            }
            if (!Schema::hasColumn('cms_workers', 'contract_end_date')) {
                $table->date('contract_end_date')->nullable()->after('contract_start_date');
            }
            if (!Schema::hasColumn('cms_workers', 'probation_end_date')) {
                $table->date('probation_end_date')->nullable()->after('contract_end_date');
            }
            
            // Salary Information (enhanced)
            if (!Schema::hasColumn('cms_workers', 'monthly_salary')) {
                $table->decimal('monthly_salary', 15, 2)->default(0)->after('commission_rate');
            }
            if (!Schema::hasColumn('cms_workers', 'salary_currency')) {
                $table->string('salary_currency')->default('ZMW')->after('monthly_salary');
            }
            
            // Tax & Statutory Information
            if (!Schema::hasColumn('cms_workers', 'tax_number')) {
                $table->string('tax_number')->nullable()->after('salary_currency');
            }
            if (!Schema::hasColumn('cms_workers', 'napsa_number')) {
                $table->string('napsa_number')->nullable()->after('tax_number');
            }
            if (!Schema::hasColumn('cms_workers', 'nhima_number')) {
                $table->string('nhima_number')->nullable()->after('napsa_number');
            }
            
            // Document Management
            if (!Schema::hasColumn('cms_workers', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('nhima_number');
            }
            if (!Schema::hasColumn('cms_workers', 'documents')) {
                $table->json('documents')->nullable()->after('photo_path');
            }
            
            // Additional Status Fields
            if (!Schema::hasColumn('cms_workers', 'employment_status')) {
                $table->enum('employment_status', ['active', 'on_leave', 'suspended', 'terminated', 'resigned'])->default('active')->after('status');
            }
            if (!Schema::hasColumn('cms_workers', 'termination_date')) {
                $table->date('termination_date')->nullable()->after('employment_status');
            }
            if (!Schema::hasColumn('cms_workers', 'termination_reason')) {
                $table->text('termination_reason')->nullable()->after('termination_date');
            }
        });
        
        // Add indexes separately to avoid duplicate index errors
        Schema::table('cms_workers', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('cms_workers');
            
            if (!isset($indexes['cms_workers_company_id_employment_status_index'])) {
                $table->index(['company_id', 'employment_status']);
            }
            if (!isset($indexes['cms_workers_company_id_department_id_index'])) {
                $table->index(['company_id', 'department_id']);
            }
            if (!isset($indexes['cms_workers_hire_date_index'])) {
                $table->index(['hire_date']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('cms_workers', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'employment_status']);
            $table->dropIndex(['company_id', 'department_id']);
            $table->dropIndex(['hire_date']);
            
            $table->dropForeign(['department_id']);
            
            $table->dropColumn([
                'first_name', 'last_name', 'middle_name',
                'date_of_birth', 'gender', 'nationality',
                'address', 'city', 'province',
                'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
                'job_title', 'department_id', 'hire_date',
                'employment_type', 'contract_start_date', 'contract_end_date', 'probation_end_date',
                'monthly_salary', 'salary_currency',
                'tax_number', 'napsa_number', 'nhima_number',
                'photo_path', 'documents',
                'employment_status', 'termination_date', 'termination_reason'
            ]);
        });
    }
};
