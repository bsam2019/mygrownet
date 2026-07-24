<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bgf_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('bgf_projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('contract_number')->unique();
            
            // Contract Terms
            $table->decimal('funding_amount', 12, 2);
            $table->decimal('member_contribution', 12, 2);
            $table->integer('member_profit_percentage');
            $table->integer('mygrownet_profit_percentage');
            $table->date('start_date');
            $table->date('end_date');
            
            // Terms & Conditions
            $table->json('terms')->nullable();
            $table->json('milestones')->nullable();
            $table->json('disbursement_schedule')->nullable();
            $table->text('special_conditions')->nullable();
            
            // Contract Document
            $table->text('contract_content'); // HTML or PDF content
            $table->string('contract_pdf_url')->nullable();
            
            // Signatures
            $table->string('member_signature')->nullable();
            $table->timestamp('member_signed_at')->nullable();
            $table->string('member_ip_address')->nullable();
            
            $table->foreignId('mygrownet_signatory_id')->nullable()->constrained('users');
            $table->string('mygrownet_signature')->nullable();
            $table->timestamp('mygrownet_signed_at')->nullable();
            
            // Status
            $table->enum('status', [
                'draft',
                'pending_member_signature',
                'pending_mygrownet_signature',
                'active',
                'completed',
                'terminated'
            ])->default('draft');
            
            $table->text('termination_reason')->nullable();
            $table->timestamp('terminated_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['project_id', 'status']);
            $table->index('contract_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bgf_contracts');
    }
};
