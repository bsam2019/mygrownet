<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('revenue_breakdown', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('financial_report_id');
            $table->string('revenue_source', 100);
            $table->decimal('amount', 15, 2);
            $table->decimal('percentage', 5, 2);
            $table->decimal('growth_rate', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('financial_report_id')->references('id')->on('investor_financial_reports')->onDelete('cascade');
            $table->index(['financial_report_id']);
            $table->index(['revenue_source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenue_breakdown');
    }
};
