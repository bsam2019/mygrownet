<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_job_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('job_id')->constrained('cms_jobs')->onDelete('cascade');
            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50);
            $table->foreignId('changed_by')->constrained('cms_users')->onDelete('restrict');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['job_id', 'created_at']);
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_job_status_history');
    }
};
