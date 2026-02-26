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
        Schema::create('storage_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->uuid('folder_id')->nullable();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('extension', 50)->nullable();
            $table->string('mime_type', 100);
            $table->bigInteger('size_bytes')->unsigned();
            $table->string('s3_bucket');
            $table->string('s3_key');
            $table->string('checksum', 64)->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('folder_id')->references('id')->on('storage_folders')->onDelete('set null');
            $table->index(['user_id', 'folder_id']);
            $table->index('original_name');
            $table->index('is_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_files');
    }
};
