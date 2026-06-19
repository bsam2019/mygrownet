<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_contract_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('cms_users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cms_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('contract_number');
            $table->foreignId('template_id')->nullable()->constrained('cms_contract_templates')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('cms_customers')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('renewal_date')->nullable();
            $table->string('status')->default('draft'); // draft, active, expired, terminated, renewed
            $table->decimal('total_value', 12, 2)->default(0);
            $table->string('currency', 3)->default('ZMW');
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('signed_by_customer')->default(false);
            $table->boolean('signed_by_company')->default(false);
            $table->timestamp('signed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('cms_users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('cms_users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'contract_number']);
        });

        Schema::create('cms_contract_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('cms_contracts')->cascadeOnDelete();
            $table->foreignId('renewed_contract_id')->nullable()->constrained('cms_contracts')->nullOnDelete();
            $table->date('renewal_date');
            $table->string('status')->default('pending'); // pending, approved, declined
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('cms_users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_contract_renewals');
        Schema::dropIfExists('cms_contracts');
        Schema::dropIfExists('cms_contract_templates');
    }
};
