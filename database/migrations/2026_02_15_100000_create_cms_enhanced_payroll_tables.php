<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Allowance Types
        if (!Schema::hasTable('cms_allowance_types')) {
            Schema::create('cms_allowance_types', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('allowance_name', 100);
                        $table->string('allowance_code', 50)->unique();
                        $table->enum('calculation_type', ['fixed', 'percentage_of_basic', 'custom'])->default('fixed');
                        $table->decimal('default_amount', 15, 2)->nullable();
                        $table->boolean('is_taxable')->default(true);
                        $table->boolean('is_pensionable')->default(false);
                        $table->boolean('is_active')->default(true);
                        $table->timestamps();
                        
                        $table->index(['company_id', 'is_active']);
                    });
        }

        // Employee Allowances
        if (!Schema::hasTable('cms_worker_allowances')) {
            Schema::create('cms_worker_allowances', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('worker_id')->constrained('cms_workers')->onDelete('cascade');
                        $table->foreignId('allowance_type_id')->constrained('cms_allowance_types')->onDelete('cascade');
                        $table->decimal('amount', 15, 2);
                        $table->date('effective_from');
                        $table->date('effective_to')->nullable();
                        $table->boolean('is_active')->default(true);
                        $table->timestamps();
                        
                        $table->index(['worker_id', 'is_active']);
                    });
        }

        // Deduction Types
        if (!Schema::hasTable('cms_deduction_types')) {
            Schema::create('cms_deduction_types', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('deduction_name', 100);
                        $table->string('deduction_code', 50)->unique();
                        $table->enum('calculation_type', ['fixed', 'percentage_of_gross', 'percentage_of_basic', 'custom'])->default('fixed');
                        $table->decimal('default_amount', 15, 2)->nullable();
                        $table->decimal('default_percentage', 5, 2)->nullable();
                        $table->boolean('is_statutory')->default(false);
                        $table->boolean('is_active')->default(true);
                        $table->timestamps();
                        
                        $table->index(['company_id', 'is_active']);
                    });
        }

        // Employee Deductions
        if (!Schema::hasTable('cms_worker_deductions')) {
            Schema::create('cms_worker_deductions', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('worker_id')->constrained('cms_workers')->onDelete('cascade');
                        $table->foreignId('deduction_type_id')->constrained('cms_deduction_types')->onDelete('cascade');
                        $table->decimal('amount', 15, 2);
                        $table->date('effective_from');
                        $table->date('effective_to')->nullable();
                        $table->boolean('is_active')->default(true);
                        $table->timestamps();
                        
                        $table->index(['worker_id', 'is_active']);
                    });
        }

        // Enhanced Payroll Items with detailed breakdown
        if (!Schema::hasTable('cms_payroll_item_details')) {
            Schema::create('cms_payroll_item_details', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('payroll_item_id')->constrained('cms_payroll_items')->onDelete('cascade');
                        $table->enum('item_type', ['allowance', 'deduction', 'overtime', 'bonus', 'commission']);
                        $table->string('item_name', 255);
                        $table->decimal('amount', 15, 2);
                        $table->timestamps();
                        
                        $table->index(['payroll_item_id', 'item_type']);
                    });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_payroll_item_details');
        Schema::dropIfExists('cms_worker_deductions');
        Schema::dropIfExists('cms_deduction_types');
        Schema::dropIfExists('cms_worker_allowances');
        Schema::dropIfExists('cms_allowance_types');
    }
};
