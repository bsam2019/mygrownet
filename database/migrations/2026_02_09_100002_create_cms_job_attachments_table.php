<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cms_job_attachments')) {
            Schema::create('cms_job_attachments', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('job_id')->constrained('cms_jobs')->onDelete('cascade');
                        $table->string('file_path', 255);
                        $table->string('file_name', 255);
                        $table->string('file_type', 50)->nullable();
                        $table->integer('file_size')->nullable();
                        $table->foreignId('uploaded_by')->constrained('cms_users');
                        $table->timestamps();
            
                        $table->index('job_id');
                    });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_job_attachments');
    }
};
