<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Document Categories
        Schema::create('cms_document_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('category_name');
            $table->string('category_code')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['company_id', 'is_active']);
        });

        // Documents Repository
        Schema::create('cms_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('cms_document_categories');
            $table->string('document_number')->unique();
            $table->string('document_name');
            $table->text('description')->nullable();
            $table->enum('document_type', ['contract', 'invoice', 'quotation', 'report', 'certificate', 'permit', 'drawing', 'specification', 'other'])->default('other');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type');
            $table->integer('file_size'); // bytes
            $table->integer('version')->default(1);
            $table->enum('status', ['draft', 'active', 'archived', 'expired'])->default('active');
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('reference_id')->nullable(); // Job, Project, Customer, etc.
            $table->string('reference_type')->nullable(); // Model class name
            $table->json('tags')->nullable();
            $table->boolean('is_confidential')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'status']);
            $table->index(['category_id']);
            $table->index(['document_type']);
            $table->index(['expiry_date']);
        });

        // Document Versions
        Schema::create('cms_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('cms_documents')->cascadeOnDelete();
            $table->integer('version_number');
            $table->string('file_path');
            $table->string('file_name');
            $table->integer('file_size');
            $table->text('change_notes')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['document_id', 'version_number']);
        });

        // Document Sharing
        Schema::create('cms_document_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('cms_documents')->cascadeOnDelete();
            $table->foreignId('shared_with_user_id')->nullable()->constrained('users');
            $table->string('shared_with_email')->nullable();
            $table->enum('permission', ['view', 'download', 'edit'])->default('view');
            $table->string('share_token')->unique()->nullable();
            $table->date('expires_at')->nullable();
            $table->foreignId('shared_by')->constrained('users');
            $table->timestamp('accessed_at')->nullable();
            $table->integer('access_count')->default(0);
            $table->timestamps();
            
            $table->index(['document_id']);
            $table->index(['share_token']);
        });

        // Document Access Log
        Schema::create('cms_document_access_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('cms_documents')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->enum('action', ['view', 'download', 'edit', 'delete', 'share'])->default('view');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('accessed_at');
            
            $table->index(['document_id', 'accessed_at']);
            $table->index(['user_id']);
        });

        // Document Templates
        Schema::create('cms_document_template_library', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->string('template_name');
            $table->text('description')->nullable();
            $table->enum('template_type', ['contract', 'agreement', 'quotation', 'invoice', 'report', 'letter', 'other'])->default('other');
            $table->string('file_path');
            $table->string('file_name');
            $table->json('placeholders')->nullable(); // Array of placeholder fields
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            
            $table->index(['company_id', 'is_active']);
        });

        // Document Signatures
        Schema::create('cms_document_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('cms_documents')->cascadeOnDelete();
            $table->string('signer_name');
            $table->string('signer_email')->nullable();
            $table->string('signer_role')->nullable();
            $table->text('signature_data'); // Base64 signature
            $table->timestamp('signed_at');
            $table->string('ip_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['document_id']);
        });

        // Document Reminders
        Schema::create('cms_document_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('cms_documents')->cascadeOnDelete();
            $table->enum('reminder_type', ['expiry', 'renewal', 'review'])->default('expiry');
            $table->date('reminder_date');
            $table->integer('days_before')->default(30);
            $table->foreignId('notify_user_id')->constrained('users');
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->index(['document_id', 'reminder_date']);
            $table->index(['is_sent']);
        });

        // Contract Management
        Schema::create('cms_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('document_id')->nullable()->constrained('cms_documents');
            $table->string('contract_number')->unique();
            $table->string('contract_title');
            $table->enum('contract_type', ['customer', 'supplier', 'employee', 'partnership', 'lease', 'other'])->default('customer');
            $table->foreignId('party_id')->nullable(); // Customer, Supplier, User ID
            $table->string('party_type')->nullable(); // Model class name
            $table->string('party_name');
            $table->decimal('contract_value', 10, 2)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('duration_months')->nullable();
            $table->enum('status', ['draft', 'active', 'expired', 'terminated', 'renewed'])->default('draft');
            $table->enum('renewal_type', ['auto', 'manual', 'none'])->default('manual');
            $table->integer('renewal_notice_days')->default(30);
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'status']);
            $table->index(['contract_type']);
            $table->index(['end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_contracts');
        Schema::dropIfExists('cms_document_reminders');
        Schema::dropIfExists('cms_document_signatures');
        Schema::dropIfExists('cms_document_template_library');
        Schema::dropIfExists('cms_document_access_log');
        Schema::dropIfExists('cms_document_shares');
        Schema::dropIfExists('cms_document_versions');
        Schema::dropIfExists('cms_documents');
        Schema::dropIfExists('cms_document_categories');
    }
};
