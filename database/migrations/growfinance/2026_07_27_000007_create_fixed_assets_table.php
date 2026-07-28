<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growfinance_fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->string('category')->nullable();
            $table->date('purchase_date');
            $table->decimal('cost', 15, 2);
            $table->decimal('residual_value', 15, 2)->default(0);
            $table->integer('useful_life_months');
            $table->string('depreciation_method')->default('straight_line'); // straight_line, reducing_balance
            $table->decimal('depreciation_rate', 5, 2)->nullable(); // e.g., 20.00 for 20% reducing balance
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->string('status')->default('active'); // active, disposed, fully_depreciated
            $table->date('disposal_date')->nullable();
            $table->decimal('disposal_proceeds', 15, 2)->nullable();
            $table->string('location')->nullable();
            $table->string('serial_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'category']);
        });

        Schema::create('growfinance_depreciation_schedule', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->date('period_date');
            $table->decimal('depreciation_amount', 15, 2);
            $table->decimal('accumulated_depreciation', 15, 2);
            $table->decimal('net_book_value', 15, 2);
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->timestamps();

            $table->foreign('asset_id')->references('id')->on('growfinance_fixed_assets')->cascadeOnDelete();
            $table->foreign('journal_entry_id')->references('id')->on('growfinance_journal_entries')->nullOnDelete();
            $table->index(['asset_id', 'period_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growfinance_depreciation_schedule');
        Schema::dropIfExists('growfinance_fixed_assets');
    }
};
