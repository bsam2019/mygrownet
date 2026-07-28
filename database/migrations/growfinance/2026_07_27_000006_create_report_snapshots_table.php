<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growfinance_report_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('users')->cascadeOnDelete();
            $table->string('report_type'); // trial_balance, profit_loss, balance_sheet, cash_flow
            $table->date('as_of_date');
            $table->json('report_data');
            $table->timestamps();

            $table->unique(['business_id', 'report_type', 'as_of_date']);
            $table->index(['business_id', 'report_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growfinance_report_snapshots');
    }
};
