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
        Schema::create('investment_rounds', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Series A - Platform Expansion"
            $table->text('description');
            $table->decimal('goal_amount', 15, 2);
            $table->decimal('raised_amount', 15, 2)->default(0);
            $table->decimal('minimum_investment', 15, 2);
            $table->decimal('valuation', 15, 2);
            $table->decimal('equity_percentage', 5, 2);
            $table->string('expected_roi'); // e.g., "3-5x"
            $table->json('use_of_funds'); // Array of {label, percentage, amount}
            $table->enum('status', ['draft', 'active', 'closed', 'completed'])->default('draft');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_rounds');
    }
};
