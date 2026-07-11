<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
            $table->foreignId('sa_bin_id')->nullable()->constrained('sa_bins')->nullOnDelete();
            $table->enum('type', [
                'purchase_in', 'sale_out', 'adjustment_in', 'adjustment_out',
                'transfer_in', 'transfer_out', 'return_in', 'return_out',
                'damage_out', 'expired_out', 'physical_count', 'opening_balance',
            ]);
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('total_value', 16, 2)->default(0);
            $table->decimal('quantity_before', 12, 2)->default(0);
            $table->decimal('quantity_after', 12, 2)->default(0);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['sa_company_id', 'sa_item_id']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_stock_movements');
    }
};
