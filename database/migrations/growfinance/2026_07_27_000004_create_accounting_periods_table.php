<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growfinance_accounting_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->constrained('growfinance_fiscal_years')->cascadeOnDelete();
            $table->string('label');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('open'); // open, closed, locked
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status'], 'acct_periods_biz_status_idx');
            $table->index(['business_id', 'start_date', 'end_date'], 'acct_periods_biz_dates_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growfinance_accounting_periods');
    }
};
