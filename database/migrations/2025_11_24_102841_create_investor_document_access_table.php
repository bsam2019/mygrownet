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
        Schema::create('investor_document_access', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('investor_account_id');
            $table->unsignedBigInteger('investor_document_id');
            $table->timestamp('accessed_at')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->foreign('investor_account_id')->references('id')->on('investor_accounts')->onDelete('cascade');
            $table->foreign('investor_document_id')->references('id')->on('investor_documents')->onDelete('cascade');
            $table->index(['investor_account_id', 'investor_document_id'], 'idx_investor_doc_access');
            $table->index(['accessed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investor_document_access');
    }
};
