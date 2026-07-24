<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->date('register_date');
            $table->decimal('opening_balance', 16, 2)->default(0);
            $table->decimal('total_sales', 16, 2)->default(0);
            $table->decimal('total_expenses', 16, 2)->default(0);
            $table->decimal('total_banking', 16, 2)->default(0);
            $table->decimal('expected_closing', 16, 2)->default(0);
            $table->decimal('actual_closing', 16, 2)->default(0);
            $table->decimal('variance', 16, 2)->default(0);
            $table->enum('status', ['open', 'closed', 'verified'])->default('open');
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['sa_company_id', 'register_date']);
        });

        Schema::create('sa_cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->foreignId('sa_cash_register_id')->constrained('sa_cash_registers')->cascadeOnDelete();
            $table->enum('type', ['sale', 'expense', 'banking', 'float_top_up', 'float_withdrawal', 'petty_cash']);
            $table->decimal('amount', 16, 2);
            $table->enum('direction', ['in', 'out']);
            $table->string('description')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_cash_movements');
        Schema::dropIfExists('sa_cash_registers');
    }
};
