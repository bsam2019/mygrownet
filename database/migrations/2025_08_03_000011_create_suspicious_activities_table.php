<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suspicious_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('activity_type'); // duplicate_account, rapid_investments, unusual_withdrawal, etc.
            $table->string('severity')->default('medium'); // low, medium, high, critical
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->json('activity_data'); // Specific data related to the suspicious activity
            $table->json('detection_rules'); // Rules that triggered the detection
            $table->boolean('is_resolved')->default(false);
            $table->string('resolution_action')->nullable(); // blocked, warned, cleared, etc.
            $table->text('admin_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['activity_type', 'severity']);
            $table->index(['ip_address', 'created_at']);
            $table->index(['is_resolved', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suspicious_activities');
    }
};