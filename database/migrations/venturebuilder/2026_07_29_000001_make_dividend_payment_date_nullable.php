<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::create('venture_dividends_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('venture_id')->constrained('ventures')->onDelete('restrict');
                $table->foreignId('shareholder_id')->constrained('venture_shareholders')->onDelete('restrict');
                $table->string('dividend_period');
                $table->date('declaration_date');
                $table->date('payment_date')->nullable();
                $table->decimal('amount', 15, 2);
                $table->decimal('equity_percentage_at_payment', 5, 4);
                $table->enum('payment_method', ['wallet', 'mobile_money', 'bank_transfer'])->default('wallet');
                $table->string('payment_reference')->nullable();
                $table->enum('status', ['declared', 'processing', 'paid', 'failed'])->default('declared');
                $table->timestamp('paid_at')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                $table->index(['venture_id', 'dividend_period']);
                $table->index(['shareholder_id', 'status']);
            });

            DB::statement('INSERT INTO venture_dividends_new SELECT * FROM venture_dividends');
            Schema::drop('venture_dividends');
            Schema::rename('venture_dividends_new', 'venture_dividends');
        } else {
            Schema::table('venture_dividends', function (Blueprint $table) {
                $table->date('payment_date')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::create('venture_dividends_old', function (Blueprint $table) {
                $table->id();
                $table->foreignId('venture_id')->constrained('ventures')->onDelete('restrict');
                $table->foreignId('shareholder_id')->constrained('venture_shareholders')->onDelete('restrict');
                $table->string('dividend_period');
                $table->date('declaration_date');
                $table->date('payment_date');
                $table->decimal('amount', 15, 2);
                $table->decimal('equity_percentage_at_payment', 5, 4);
                $table->enum('payment_method', ['wallet', 'mobile_money', 'bank_transfer'])->default('wallet');
                $table->string('payment_reference')->nullable();
                $table->enum('status', ['declared', 'processing', 'paid', 'failed'])->default('declared');
                $table->timestamp('paid_at')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                $table->index(['venture_id', 'dividend_period']);
                $table->index(['shareholder_id', 'status']);
            });

            DB::statement('INSERT INTO venture_dividends_old SELECT * FROM venture_dividends');
            Schema::drop('venture_dividends');
            Schema::rename('venture_dividends_old', 'venture_dividends');
        } else {
            Schema::table('venture_dividends', function (Blueprint $table) {
                $table->date('payment_date')->nullable(false)->change();
            });
        }
    }
};
