<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_customer_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('cms_customers')->onDelete('cascade');
            $table->enum('document_type', ['contract', 'design', 'quote', 'other']);
            $table->string('file_path', 255);
            $table->string('file_name', 255);
            $table->integer('file_size')->nullable();
            $table->foreignId('uploaded_by')->constrained('cms_users');
            $table->timestamps();

            $table->index(['customer_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_customer_documents');
    }
};
