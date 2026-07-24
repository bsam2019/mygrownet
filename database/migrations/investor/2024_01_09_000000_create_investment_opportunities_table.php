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
        Schema::create('investment_opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->decimal('minimum_investment', 15, 2);
            $table->decimal('expected_returns', 5, 2); // Percentage
            $table->string('status')->default('active');
            $table->foreignId('category_id')->nullable()->constrained('investment_categories')->onDelete('set null');
            $table->integer('duration')->nullable(); // In months
            $table->string('risk_level')->nullable(); // low, medium, high
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_opportunities');
    }
}; 