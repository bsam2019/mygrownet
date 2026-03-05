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
        Schema::create('quick_invoice_attachment_library', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // User-friendly name for the attachment
            $table->string('original_filename');
            $table->string('path'); // S3 path
            $table->string('type'); // MIME type
            $table->integer('size'); // File size in bytes
            $table->text('description')->nullable(); // Optional description
            $table->timestamps();
            
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quick_invoice_attachment_library');
    }
};
