<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quarterly_profit_shares', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('quarter'); // 1, 2, 3, 4
            $table->decimal('total_project_profit', 15, 2);
            $table->decimal('member_share_amount', 15, 2); // 60% of total
            $table->decimal('company_retained', 15, 2); // 40% of total
            $table->integer('total_active_members');
            $table->decimal('total_bp_pool', 15, 2)->nullable();
            $table->enum('distribution_method', ['bp_based', 'level_based', 'equal']);
            $table->enum('status', ['draft', 'calculated', 'approved', 'distributed'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('distributed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['year', 'quarter']);
        });

        Schema::create('member_profit_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quarterly_profit_share_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('professional_level');
            $table->decimal('level_multiplier', 4, 2);
            $table->decimal('member_bp', 15, 2)->nullable();
            $table->decimal('share_amount', 10, 2);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_profit_shares');
        Schema::dropIfExists('quarterly_profit_shares');
    }
};
