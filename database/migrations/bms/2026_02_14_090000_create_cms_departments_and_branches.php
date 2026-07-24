<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Branches table
        if (!Schema::hasTable('cms_branches')) {
            Schema::create('cms_branches', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                        $table->string('branch_code')->unique();
                        $table->string('branch_name');
                        $table->string('phone')->nullable();
                        $table->string('email')->nullable();
                        $table->text('address')->nullable();
                        $table->string('city')->nullable();
                        $table->string('province')->nullable();
                        $table->boolean('is_head_office')->default(false);
                        $table->boolean('is_active')->default(true);
                        $table->foreignId('manager_id')->nullable()->constrained('cms_users')->nullOnDelete();
                        $table->timestamps();
            
                        $table->index(['company_id', 'is_active']);
                        $table->unique(['company_id', 'branch_code']);
                    });
        }

        // Departments table
        if (!Schema::hasTable('cms_departments')) {
            Schema::create('cms_departments', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                        $table->foreignId('branch_id')->nullable()->constrained('cms_branches')->nullOnDelete();
                        $table->string('department_code')->unique();
                        $table->string('department_name');
                        $table->text('description')->nullable();
                        $table->foreignId('manager_id')->nullable()->constrained('cms_users')->nullOnDelete();
                        $table->foreignId('parent_department_id')->nullable()->constrained('cms_departments')->nullOnDelete();
                        $table->boolean('is_active')->default(true);
                        $table->timestamps();
            
                        $table->index(['company_id', 'is_active']);
                        $table->index(['company_id', 'branch_id']);
                        $table->unique(['company_id', 'department_code']);
                    });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_departments');
        Schema::dropIfExists('cms_branches');
    }
};
