<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('investor_share_certificates')) {
            Schema::create('investor_share_certificates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
                $table->string('certificate_number', 50)->unique();
                $table->decimal('shares_percentage', 10, 4);
                $table->decimal('investment_amount', 15, 2);
                $table->date('issue_date');
                $table->string('pdf_path')->nullable();
                $table->string('verification_hash')->nullable();
                $table->string('status')->default('issued');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('investor_dividends')) {
            Schema::create('investor_dividends', function (Blueprint $table) {
                $table->id();
                $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
                $table->string('dividend_period');
                $table->decimal('gross_amount', 15, 2);
                $table->decimal('tax_withheld', 15, 2)->default(0);
                $table->decimal('net_amount', 15, 2);
                $table->date('declaration_date');
                $table->date('payment_date')->nullable();
                $table->string('status')->default('declared');
                $table->string('payment_method')->nullable();
                $table->string('payment_reference')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('investor_payment_methods')) {
            Schema::create('investor_payment_methods', function (Blueprint $table) {
                $table->id();
                $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
                $table->string('method_type')->default('bank_transfer');
                $table->string('bank_name')->nullable();
                $table->string('account_number')->nullable();
                $table->string('account_name')->nullable();
                $table->string('branch_code')->nullable();
                $table->string('mobile_provider')->nullable();
                $table->string('mobile_number')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->boolean('is_verified')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('investor_relations_documents')) {
            Schema::create('investor_relations_documents', function (Blueprint $table) {
                $table->id();
                $table->string('document_type');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('period')->nullable();
                $table->string('file_path');
                $table->string('file_type', 10);
                $table->integer('file_size')->nullable();
                $table->date('document_date');
                $table->boolean('is_public')->default(false);
                $table->boolean('requires_acknowledgment')->default(false);
                $table->string('status')->default('published');
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('investor_document_access_log')) {
            Schema::create('investor_document_access_log', function (Blueprint $table) {
                $table->id();
                $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
                $table->foreignId('document_id')->constrained('investor_relations_documents')->onDelete('cascade');
                $table->timestamp('accessed_at');
                $table->boolean('downloaded')->default(false);
                $table->boolean('acknowledged')->default(false);
            });
        }

        if (!Schema::hasTable('investor_relations_updates')) {
            Schema::create('investor_relations_updates', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('content');
                $table->string('update_type')->default('general');
                $table->string('priority')->default('medium');
                $table->date('publish_date');
                $table->boolean('send_notification')->default(false);
                $table->boolean('is_published')->default(false);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('investor_relations_updates');
        Schema::dropIfExists('investor_document_access_log');
        Schema::dropIfExists('investor_relations_documents');
        Schema::dropIfExists('investor_payment_methods');
        Schema::dropIfExists('investor_dividends');
        Schema::dropIfExists('investor_share_certificates');
    }
};
