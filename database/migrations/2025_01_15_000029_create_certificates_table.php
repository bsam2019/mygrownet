<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recognition_event_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('certificate_number')->unique();
            $table->enum('certificate_type', [
                'achievement', 
                'tier_advancement', 
                'course_completion', 
                'recognition_award', 
                'leadership', 
                'community_service', 
                'mentorship', 
                'annual_recognition'
            ]);
            $table->string('title');
            $table->text('description');
            $table->datetime('issued_date');
            $table->json('template_data')->nullable();
            $table->string('pdf_path')->nullable();
            $table->boolean('is_digital')->default(true);
            $table->string('verification_code')->unique();
            $table->enum('status', ['pending', 'issued', 'revoked'])->default('pending');
            $table->timestamps();

            $table->index(['certificate_type']);
            $table->index(['issued_date']);
            $table->index(['status']);
            $table->index(['verification_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};