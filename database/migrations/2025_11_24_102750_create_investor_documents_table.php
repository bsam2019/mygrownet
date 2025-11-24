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
        Schema::create('investor_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category', 100);
            $table->string('file_path', 500);
            $table->string('file_name');
            $table->integer('file_size');
            $table->string('mime_type', 100);
            $table->timestamp('upload_date')->useCurrent();
            $table->unsignedBigInteger('uploaded_by');
            $table->boolean('visible_to_all')->default(true);
            $table->unsignedBigInteger('investment_round_id')->nullable();
            $table->string('status', 50)->default('active');
            $table->integer('download_count')->default(0);
            $table->timestamps();
            
            $table->foreign('uploaded_by')->references('id')->on('users');
            $table->foreign('investment_round_id')->references('id')->on('investment_rounds');
            $table->index(['category']);
            $table->index(['status']);
            $table->index(['upload_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investor_documents');
    }
};
