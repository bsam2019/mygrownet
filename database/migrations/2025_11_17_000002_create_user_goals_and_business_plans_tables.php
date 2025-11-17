<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // User Goals table
        Schema::create('user_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('goal_type', ['monthly_income', 'team_size', 'total_earnings']);
            $table->decimal('target_amount', 10, 2);
            $table->date('target_date');
            $table->text('description')->nullable();
            $table->decimal('current_progress', 10, 2)->default(0);
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
        });
        
        // User Business Plans table
        Schema::create('user_business_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('business_name');
            $table->text('vision');
            $table->text('target_market');
            $table->decimal('income_goal_6months', 10, 2);
            $table->decimal('income_goal_1year', 10, 2);
            $table->integer('team_size_goal');
            $table->text('marketing_strategy');
            $table->text('action_plan');
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_business_plans');
        Schema::dropIfExists('user_goals');
    }
};
