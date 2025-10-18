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
        Schema::create('profit_distributions', function (Blueprint $table) {
            $table->id();
            $table->enum('period_type', ['annual', 'quarterly']); // Type of distribution
            $table->date('period_start'); // Start date of the period
            $table->date('period_end'); // End date of the period
            $table->decimal('total_profit', 15, 2); // Total profit for the period
            $table->decimal('distribution_percentage', 5, 2); // Percentage allocated for distribution
            $table->decimal('total_distributed', 15, 2)->default(0); // Total amount distributed
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable(); // Additional notes
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes for efficient queries
            $table->index(['period_type', 'status']);
            $table->index(['period_start', 'period_end']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profit_distributions');
    }
};
