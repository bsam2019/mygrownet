<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawal_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('withdrawal_type', ['early', 'full', 'partial']);
            $table->decimal('amount', 15, 2);
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected', 'processed'])->default('pending');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['approval_status', 'processed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_policies');
    }
};