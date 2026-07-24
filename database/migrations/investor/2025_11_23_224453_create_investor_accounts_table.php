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
        Schema::create('investor_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->decimal('investment_amount', 15, 2);
            $table->date('investment_date');
            $table->foreignId('investment_round_id')->constrained('investment_rounds')->cascadeOnDelete();
            $table->enum('status', ['ciu', 'shareholder', 'exited'])->default('ciu');
            $table->decimal('equity_percentage', 5, 4)->default(0);
            $table->timestamps();

            $table->index('user_id');
            $table->index('email');
            $table->index('investment_round_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investor_accounts');
    }
};
