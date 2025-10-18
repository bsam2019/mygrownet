<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tier_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tier_id')->constrained('investment_tiers')->onDelete('cascade');
            $table->date('qualification_month'); // Month being tracked (YYYY-MM-01)
            $table->boolean('qualifies')->default(false);
            $table->decimal('team_volume', 15, 2)->default(0);
            $table->integer('active_referrals')->default(0);
            $table->decimal('required_team_volume', 15, 2)->default(0);
            $table->integer('required_active_referrals')->default(0);
            $table->integer('consecutive_months')->default(0); // Running count of consecutive months
            $table->boolean('permanent_status')->default(false);
            $table->timestamp('permanent_achieved_at')->nullable();
            $table->timestamps();
            
            // Indexes for efficient queries
            $table->unique(['user_id', 'tier_id', 'qualification_month']);
            $table->index(['user_id', 'qualification_month']);
            $table->index(['tier_id', 'qualifies', 'qualification_month']);
            $table->index(['permanent_status', 'tier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tier_qualifications');
    }
};