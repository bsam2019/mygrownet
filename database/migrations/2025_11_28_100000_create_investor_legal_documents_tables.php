<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Share Certificates
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
            $table->enum('status', ['draft', 'issued', 'cancelled'])->default('issued');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('certificate_number');
            $table->index('investor_account_id');
        });
        }

        // Legal Documents Library
        if (!Schema::hasTable('investor_legal_documents')) {
        Schema::create('investor_legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('document_type', [
                'shareholder_agreement',
                'articles_of_association',
                'compliance_certificate',
                'audit_report',
                'tax_document',
                'other'
            ]);
            $table->string('file_path');
            $table->string('file_name');
            $table->integer('file_size')->nullable();
            $table->date('document_date');
            $table->boolean('is_public')->default(false); // Available to all investors
            $table->json('metadata')->nullable(); // Additional document info
            $table->timestamps();
            
            $table->index('document_type');
            $table->index('document_date');
        });
        }

        // Document Access Log (track who accessed what)
        if (!Schema::hasTable('investor_document_access_log')) {
        Schema::create('investor_document_access_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
            $table->foreignId('legal_document_id')->nullable()->constrained('investor_legal_documents')->onDelete('set null');
            $table->string('document_type');
            $table->string('action'); // viewed, downloaded
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('accessed_at');
            
            $table->index(['investor_account_id', 'accessed_at'], 'doc_access_account_time_idx');
        });
        }

        // Investor-Specific Document Access (for restricted documents)
        if (!Schema::hasTable('investor_document_permissions')) {
        Schema::create('investor_document_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
            $table->foreignId('legal_document_id')->constrained('investor_legal_documents')->onDelete('cascade');
            $table->timestamp('granted_at');
            $table->timestamp('expires_at')->nullable();
            
            $table->unique(['investor_account_id', 'legal_document_id'], 'inv_doc_perm_unique');
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('investor_document_permissions');
        Schema::dropIfExists('investor_document_access_log');
        Schema::dropIfExists('investor_legal_documents');
        Schema::dropIfExists('investor_share_certificates');
    }
};
