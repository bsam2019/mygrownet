<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['mtn_momo', 'airtel_money', 'bank_transfer', 'cash', 'other']);
            $table->string('payment_reference')->unique();
            $table->string('phone_number');
            $table->string('account_name');
            $table->enum('payment_type', ['wallet_topup', 'subscription', 'workshop', 'product', 'learning_pack', 'coaching', 'upgrade', 'other']);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_payments');
    }
};
