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
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('middle_name')->nullable()->after('last_name');
            $table->date('date_of_birth')->nullable()->after('id_number');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            $table->string('nationality')->default('Zambian')->after('gender');
            $table->text('address')->nullable()->after('nationality');
            $table->string('city')->nullable()->after('address');
            $table->string('province')->nullable()->after('city');
            
            // Emergency Contact
            $table->string('emergency_contact_name')->nullable()->after('province');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_phone');
            
            // Employment Details
            $table->string('job_title')->nullable()->after('worker_type');
            $table->foreignId('department_id')->nullable()->constrained('cms_departments')->nullOnDelete()->after('job_title');
            $table->date('hire_date')->nullable()->after('department_id');
            $table->enum('employment_type', ['casual', 'contract', 'permanent', 'part_time', 'intern'])->default('casual')->after('hire_date');
            $table->date('contract_start_date')->nullable()->after('employment_type');
            $table->date('contract_end_date')->nullable()->after('contract_start_date');
            $table->date('probation_end_date')->nullable()->after('contract_end_date');
            
            // Salary Information (enhanced)
            $table->decimal('monthly_salary', 15, 2)->default(0)->after('commission_rate');
            $table->string('salary_currency')->default('ZMW')->after('monthly_salary');
            
            // Tax & Statutory Information
            $table->string('tax_number')->nullable()->after('salary_currency'); // TPIN
            $table->string('napsa_number')->nullable()->after('tax_number');
            $table->string('nhima_number')->nullable()->after('napsa_number');
            
            // Document Management
            $table->string('photo_path')->nullable()->after('nhima_number');
            $table->json('documents')->nullable()->after('photo_path'); // Store document references
            
            // Additional Status Fields
            $table->enum('employment_status', ['active', 'on_leave', 'suspended', 'terminated', 'resigned'])->default('active')->after('status');
            $table->date('termination_date')->nullable()->after('employment_status');
            $table->text('termination_reason')->nullable()->after('termination_date');
            
            // Indexes for new fields
            $table->index(['company_id', 'employment_status']);
            $table->index(['company_id', 'department_id']);
            $table->index(['hire_date']);
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
