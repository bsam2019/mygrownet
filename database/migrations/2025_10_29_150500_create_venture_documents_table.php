<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venture_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venture_id')->constrained('ventures')->onDelete('cascade');
            
            // Document Details
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', [
                'business_plan',
                'financial_report',
                'shareholder_agreement',
                'certificate',
                'compliance_document',
                'update_report',
                'other'
            ]);
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type');
            $table->integer('file_size'); // in bytes
            
            // Access Control
            $table->enum('visibility', ['public', 'investors_only', 'shareholders_only', 'admin_only'])->default('investors_only');
            $table->boolean('is_confidential')->default(false);
            
            // Metadata
            $table->integer('download_count')->default(0);
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('restrict');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['venture_id', 'type']);
            $table->index('visibility');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venture_documents');
    }
};
