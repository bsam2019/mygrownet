<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stock Locations/Warehouses (NEW)
        if (!Schema::hasTable('cms_stock_locations')) {
            Schema::create('cms_stock_locations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                $table->string('location_code')->unique();
                $table->string('location_name');
                $table->enum('location_type', ['warehouse', 'workshop', 'site', 'vehicle', 'other'])->default('warehouse');
                $table->text('address')->nullable();
                $table->foreignId('manager_id')->nullable()->constrained('users');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index(['company_id', 'is_active']);
            });
        }

        // Stock Levels (NEW - Real-time inventory per location)
        if (!Schema::hasTable('cms_stock_levels')) {
            Schema::create('cms_stock_levels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                $table->foreignId('material_id')->constrained('cms_materials');
                $table->foreignId('location_id')->constrained('cms_stock_locations');
                $table->decimal('quantity', 15, 4)->default(0);
                $table->decimal('reserved_quantity', 15, 4)->default(0);
                $table->decimal('available_quantity', 15, 4)->default(0);
                $table->decimal('reorder_level', 15, 4)->nullable();
                $table->decimal('reorder_quantity', 15, 4)->nullable();
                $table->decimal('max_stock_level', 15, 4)->nullable();
                $table->decimal('min_stock_level', 15, 4)->nullable();
                $table->timestamp('last_counted_at')->nullable();
                $table->timestamps();
                
                $table->unique(['material_id', 'location_id']);
                $table->index(['company_id', 'material_id']);
                $table->index(['location_id']);
            });
        }

        // Enhance existing stock_movements table
        if (Schema::hasTable('cms_stock_movements') && !Schema::hasColumn('cms_stock_movements', 'from_location_id')) {
            Schema::table('cms_stock_movements', function (Blueprint $table) {
                $table->foreignId('from_location_id')->nullable()->after('material_id')->constrained('cms_stock_locations');
                $table->foreignId('to_location_id')->nullable()->after('from_location_id')->constrained('cms_stock_locations');
                $table->string('movement_number')->unique()->after('to_location_id');
            });
        }

        // Stock Adjustments (NEW)
        if (!Schema::hasTable('cms_stock_adjustments')) {
            Schema::create('cms_stock_adjustments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                $table->string('adjustment_number')->unique();
                $table->foreignId('location_id')->constrained('cms_stock_locations');
                $table->date('adjustment_date');
                $table->enum('adjustment_type', ['increase', 'decrease', 'correction'])->default('correction');
                $table->enum('reason', ['damaged', 'expired', 'found', 'lost', 'count_correction', 'other'])->default('count_correction');
                $table->text('notes')->nullable();
                $table->foreignId('adjusted_by')->constrained('users');
                $table->foreignId('approved_by')->nullable()->constrained('users');
                $table->timestamp('approved_at')->nullable();
                $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected'])->default('draft');
                $table->timestamps();
                
                $table->index(['company_id', 'adjustment_date']);
                $table->index(['location_id']);
            });
        }

        // Stock Adjustment Items (NEW)
        if (!Schema::hasTable('cms_stock_adjustment_items')) {
            Schema::create('cms_stock_adjustment_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('adjustment_id')->constrained('cms_stock_adjustments')->cascadeOnDelete();
                $table->foreignId('material_id')->constrained('cms_materials');
                $table->decimal('current_quantity', 15, 4);
                $table->decimal('adjusted_quantity', 15, 4);
                $table->decimal('variance', 15, 4);
                $table->decimal('unit_cost', 10, 2)->nullable();
                $table->decimal('total_value', 10, 2)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index(['adjustment_id']);
                $table->index(['material_id']);
            });
        }

        // Stock Transfers (NEW)
        if (!Schema::hasTable('cms_stock_transfers')) {
            Schema::create('cms_stock_transfers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                $table->string('transfer_number')->unique();
                $table->foreignId('from_location_id')->constrained('cms_stock_locations');
                $table->foreignId('to_location_id')->constrained('cms_stock_locations');
                $table->date('transfer_date');
                $table->enum('status', ['pending', 'in_transit', 'received', 'cancelled'])->default('pending');
                $table->foreignId('requested_by')->constrained('users');
                $table->foreignId('approved_by')->nullable()->constrained('users');
                $table->foreignId('received_by')->nullable()->constrained('users');
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index(['company_id', 'transfer_date']);
                $table->index(['from_location_id']);
                $table->index(['to_location_id']);
            });
        }

        // Stock Transfer Items (NEW)
        if (!Schema::hasTable('cms_stock_transfer_items')) {
            Schema::create('cms_stock_transfer_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transfer_id')->constrained('cms_stock_transfers')->cascadeOnDelete();
                $table->foreignId('material_id')->constrained('cms_materials');
                $table->decimal('quantity', 15, 4);
                $table->decimal('received_quantity', 15, 4)->nullable();
                $table->string('unit');
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index(['transfer_id']);
                $table->index(['material_id']);
            });
        }

        // Stock Counts (NEW)
        if (!Schema::hasTable('cms_stock_counts')) {
            Schema::create('cms_stock_counts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                $table->string('count_number')->unique();
                $table->foreignId('location_id')->constrained('cms_stock_locations');
                $table->date('count_date');
                $table->enum('count_type', ['full', 'partial', 'cycle'])->default('full');
                $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
                $table->foreignId('counted_by')->constrained('users');
                $table->foreignId('verified_by')->nullable()->constrained('users');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index(['company_id', 'count_date']);
                $table->index(['location_id']);
            });
        }

        // Stock Count Items (NEW)
        if (!Schema::hasTable('cms_stock_count_items')) {
            Schema::create('cms_stock_count_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('count_id')->constrained('cms_stock_counts')->cascadeOnDelete();
                $table->foreignId('material_id')->constrained('cms_materials');
                $table->decimal('system_quantity', 15, 4);
                $table->decimal('counted_quantity', 15, 4)->nullable();
                $table->decimal('variance', 15, 4)->nullable();
                $table->decimal('variance_percentage', 5, 2)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index(['count_id']);
                $table->index(['material_id']);
            });
        }

        // Stock Valuation History (NEW - for FIFO/LIFO)
        if (!Schema::hasTable('cms_stock_valuation')) {
            Schema::create('cms_stock_valuation', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                $table->foreignId('material_id')->constrained('cms_materials');
                $table->foreignId('location_id')->constrained('cms_stock_locations');
                $table->date('valuation_date');
                $table->decimal('quantity', 15, 4);
                $table->decimal('unit_cost', 10, 2);
                $table->decimal('total_value', 10, 2);
                $table->enum('valuation_method', ['fifo', 'lifo', 'average'])->default('fifo');
                $table->timestamps();
                
                $table->index(['company_id', 'valuation_date']);
                $table->index(['material_id', 'location_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_stock_valuation');
        Schema::dropIfExists('cms_stock_count_items');
        Schema::dropIfExists('cms_stock_counts');
        Schema::dropIfExists('cms_stock_transfer_items');
        Schema::dropIfExists('cms_stock_transfers');
        Schema::dropIfExists('cms_stock_adjustment_items');
        Schema::dropIfExists('cms_stock_adjustments');
        
        if (Schema::hasColumn('cms_stock_movements', 'from_location_id')) {
            Schema::table('cms_stock_movements', function (Blueprint $table) {
                $table->dropForeign(['from_location_id']);
                $table->dropForeign(['to_location_id']);
                $table->dropColumn(['from_location_id', 'to_location_id', 'movement_number']);
            });
        }
        
        Schema::dropIfExists('cms_stock_levels');
        Schema::dropIfExists('cms_stock_locations');
    }
};
