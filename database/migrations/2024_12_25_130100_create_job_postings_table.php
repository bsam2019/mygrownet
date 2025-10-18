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
        // Skip if table already exists (idempotent migration during partial runs)
        if (Schema::hasTable('job_postings')) {
            return;
        }
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            // Use plain keys + indexes to avoid FK failures when referenced tables are created later
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('department_id');
            $table->string('title');
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->text('benefits')->nullable();
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'internship'])->default('full_time');
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('posted_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['is_active', 'posted_at']);
            $table->index(['department_id', 'is_active']);
            $table->index('position_id');
            $table->index('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
