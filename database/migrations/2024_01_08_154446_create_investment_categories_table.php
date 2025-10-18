<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('investment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                    // Category name (e.g., "Real Estate Fund")
            $table->string('slug')->unique();                         // URL-friendly version of name
            $table->text('description')->nullable();                  // Detailed description of the investment category
            $table->decimal('min_investment', 12, 2)->default(0);     // Minimum amount required to invest
            $table->decimal('max_investment', 12, 2)->nullable();     // Maximum investment limit (optional)
            $table->decimal('interest_rate', 5, 2);                  // Annual interest rate for this category
            $table->decimal('expected_roi', 5, 2);                   // Expected return on investment percentage
            $table->integer('lock_in_period')->default(0);           // Minimum holding period in months
            $table->boolean('is_active')->default(true);             // Whether this category is available for new investments
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('investment_categories');
    }
};
