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
        if (!Schema::hasTable('cms_customers')) {
            Schema::create('cms_customers', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->string('customer_number', 50);
                        $table->string('name');
                        $table->string('email')->nullable();
                        $table->string('phone', 50)->nullable();
                        $table->string('secondary_phone', 50)->nullable();
                        $table->text('address')->nullable();
                        $table->string('city', 100)->nullable();
                        $table->string('country', 100)->default('Zambia');
                        $table->string('tax_number', 100)->nullable();
                        $table->decimal('credit_limit', 15, 2)->default(0);
                        $table->decimal('outstanding_balance', 15, 2)->default(0);
                        $table->enum('status', ['active', 'inactive'])->default('active');
                        $table->text('notes')->nullable();
                        $table->foreignId('created_by')->constrained('cms_users');
                        $table->timestamps();
            
                        $table->unique(['company_id', 'customer_number'], 'unique_customer_number');
                        $table->index(['company_id', 'status'], 'idx_company_status');
                        $table->index(['company_id', 'outstanding_balance'], 'idx_outstanding');
                    });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_customers');
    }
};
