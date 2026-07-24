<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growfinance_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('payable'); // payable_type, payable_id
            $table->date('payment_date');
            $table->decimal('amount', 15, 2)->default(0);
            $table->enum('payment_method', ['cash', 'bank', 'mobile_money', 'cheque'])->default('cash');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growfinance_payments');
    }
};
