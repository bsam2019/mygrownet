<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growfinance_tax_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->string('tax_type'); // vat, withholding, sales_tax, other
            $table->decimal('rate', 5, 2);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('jurisdiction')->default('ZM');
            $table->string('account_code')->nullable(); // links to chart of accounts
            $table->string('gl_code')->nullable(); // e.g., 2400 for VAT, 2500 for WHT
            $table->boolean('is_default')->default(false);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['business_id', 'tax_type', 'is_active'], 'tax_rates_biz_type_active_idx');
        });

        Schema::create('growfinance_tax_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('return_type'); // vat, withholding
            $table->string('period_label'); // e.g., "July 2026"
            $table->date('period_start');
            $table->date('period_end');
            $table->date('due_date')->nullable();
            $table->decimal('output_vat', 15, 2)->default(0);
            $table->decimal('input_vat', 15, 2)->default(0);
            $table->decimal('net_vat_payable', 15, 2)->default(0);
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->decimal('total_purchases', 15, 2)->default(0);
            $table->decimal('withholding_collected', 15, 2)->default(0);
            $table->decimal('withholding_paid', 15, 2)->default(0);
            $table->string('status')->default('draft'); // draft, filed, paid
            $table->date('filed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['business_id', 'return_type', 'period_start', 'period_end'], 'tax_returns_biz_type_period_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growfinance_tax_returns');
        Schema::dropIfExists('growfinance_tax_rates');
    }
};
