<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->decimal('credit_limit', 14, 2)->nullable();
            $table->string('payment_terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['sa_company_id', 'name']);
        });

        Schema::table('sa_sales', function (Blueprint $table) {
            $table->foreignId('sa_customer_id')->nullable()->constrained('sa_customers')->nullOnDelete();
        });

        Schema::table('sa_quotations', function (Blueprint $table) {
            $table->foreignId('sa_customer_id')->nullable()->constrained('sa_customers')->nullOnDelete();
        });

        Schema::table('sa_invoices', function (Blueprint $table) {
            $table->foreignId('sa_customer_id')->nullable()->constrained('sa_customers')->nullOnDelete();
        });

        Schema::table('sa_receipts', function (Blueprint $table) {
            $table->foreignId('sa_customer_id')->nullable()->constrained('sa_customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sa_receipts', fn($t) => $t->dropForeign(['sa_customer_id']));
        Schema::table('sa_invoices', fn($t) => $t->dropForeign(['sa_customer_id']));
        Schema::table('sa_quotations', fn($t) => $t->dropForeign(['sa_customer_id']));
        Schema::table('sa_sales', fn($t) => $t->dropForeign(['sa_customer_id']));
        Schema::dropIfExists('sa_customers');
    }
};
