<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('id_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('document_type'); // national_id, passport, drivers_license
            $table->string('document_number');
            $table->string('document_front_path'); // File path to front image
            $table->string('document_back_path')->nullable(); // File path to back image
            $table->string('selfie_path')->nullable(); // File path to selfie with document
            $table->string('status')->default('pending'); // pending, approved, rejected, expired
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('expires_at')->nullable();
            $table->json('verification_data')->nullable(); // OCR results, face match scores, etc.
            $table->timestamps();

            $table->unique(['document_type', 'document_number']);
            $table->index(['status', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('id_verifications');
    }
};