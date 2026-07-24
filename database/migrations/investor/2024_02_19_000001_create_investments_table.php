<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('investment_categories')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->decimal('platform_fee', 10, 2)->default(0.00);
            $table->string('tier')->default('Basic');
            $table->decimal('expected_return', 15, 2)->default(0.00);
            $table->decimal('roi', 15, 2)->default(0.00);
            $table->decimal('total_earned', 15, 2)->default(0);
            $table->enum('status', ['pending', 'active', 'withdrawn', 'terminated'])->default('pending');
            $table->timestamp('investment_date');
            $table->decimal('interest_earned', 10, 2)->default(0);
            $table->timestamp('lock_in_period_end');
            $table->timestamp('last_payout_date')->nullable();
            $table->timestamp('maturity_date')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['tier', 'status']);
            $table->index(['investment_date', 'maturity_date']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('investments');
        Schema::enableForeignKeyConstraints();
    }

};
