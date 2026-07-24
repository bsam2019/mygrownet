<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Main assets table
        if (!Schema::hasTable('cms_assets')) {
            Schema::create('cms_assets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                $table->string('asset_number')->unique(); // AUTO-0001
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('category'); // Equipment, Vehicle, Computer, Furniture, etc.
                $table->string('serial_number')->nullable();
                $table->string('model')->nullable();
                $table->string('manufacturer')->nullable();
                $table->date('purchase_date')->nullable();
                $table->decimal('purchase_cost', 15, 2)->default(0);
                $table->decimal('current_value', 15, 2)->default(0);
                $table->string('condition')->default('good'); // excellent, good, fair, poor
                $table->string('status')->default('available'); // available, in_use, maintenance, retired
                $table->string('location')->nullable();
                $table->foreignId('assigned_to')->nullable()->constrained('cms_users')->nullOnDelete();
                $table->date('assigned_date')->nullable();
                $table->integer('warranty_months')->nullable();
                $table->date('warranty_expiry')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->constrained('cms_users')->cascadeOnDelete();
                $table->timestamps();

                $table->index(['company_id', 'status']);
                $table->index(['company_id', 'category']);
                $table->index(['company_id', 'assigned_to']);
                $table->unique(['company_id', 'asset_number']);
            });
        }

        // Asset assignments history
        if (!Schema::hasTable('cms_asset_assignments')) {
            Schema::create('cms_asset_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                $table->foreignId('asset_id')->constrained('cms_assets')->cascadeOnDelete();
                $table->foreignId('assigned_to')->constrained('cms_users')->cascadeOnDelete();
                $table->date('assigned_date');
                $table->date('returned_date')->nullable();
                $table->string('condition_on_assignment')->default('good');
                $table->string('condition_on_return')->nullable();
                $table->text('assignment_notes')->nullable();
                $table->text('return_notes')->nullable();
                $table->foreignId('assigned_by')->constrained('cms_users')->cascadeOnDelete();
                $table->foreignId('returned_by')->nullable()->constrained('cms_users')->nullOnDelete();
                $table->timestamps();

                $table->index(['company_id', 'asset_id']);
                $table->index(['company_id', 'assigned_to']);
            });
        }

        // Asset maintenance records
        if (!Schema::hasTable('cms_asset_maintenance')) {
            Schema::create('cms_asset_maintenance', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                $table->foreignId('asset_id')->constrained('cms_assets')->cascadeOnDelete();
                $table->string('maintenance_type'); // routine, repair, inspection, upgrade
                $table->text('description');
                $table->date('scheduled_date')->nullable();
                $table->date('completed_date')->nullable();
                $table->decimal('cost', 15, 2)->default(0);
                $table->string('performed_by')->nullable(); // Internal staff or external vendor
                $table->string('status')->default('scheduled'); // scheduled, in_progress, completed, cancelled
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->constrained('cms_users')->cascadeOnDelete();
                $table->timestamps();

                $table->index(['company_id', 'asset_id']);
                $table->index(['company_id', 'status']);
                $table->index(['scheduled_date']);
            });
        }

        // Asset depreciation records
        if (!Schema::hasTable('cms_asset_depreciation')) {
            Schema::create('cms_asset_depreciation', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                $table->foreignId('asset_id')->constrained('cms_assets')->cascadeOnDelete();
                $table->string('method')->default('straight_line'); // straight_line, declining_balance
                $table->integer('useful_life_years');
                $table->decimal('salvage_value', 15, 2)->default(0);
                $table->decimal('annual_depreciation', 15, 2)->default(0);
                $table->date('depreciation_start_date');
                $table->timestamps();

                $table->index(['company_id', 'asset_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_asset_depreciation');
        Schema::dropIfExists('cms_asset_maintenance');
        Schema::dropIfExists('cms_asset_assignments');
        Schema::dropIfExists('cms_assets');
    }
};
