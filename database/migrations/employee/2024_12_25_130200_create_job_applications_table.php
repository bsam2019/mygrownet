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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->string('national_id')->nullable();
            $table->text('address')->nullable();
            $table->string('resume_path')->nullable();
            $table->text('cover_letter')->nullable();
            $table->decimal('expected_salary', 12, 2)->nullable();
            $table->enum('status', ['submitted', 'under_review', 'interviewed', 'hired', 'rejected'])->default('submitted');
            $table->text('admin_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();
            
            $table->index(['status', 'applied_at']);
            $table->index(['job_posting_id', 'status']);
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
