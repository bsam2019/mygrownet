<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('referral_commissions')) {
            Schema::create('referral_commissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('referee_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('investment_id')->constrained('investments')->onDelete('cascade');
                $table->integer('level')->default(1);
                $table->decimal('commission_amount', 15, 2);
                $table->decimal('commission_rate', 5, 2);
                $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->index(['referrer_id', 'status']);
                $table->index(['investment_id', 'level']);
                $table->index(['referee_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_commissions');
    }
}; 