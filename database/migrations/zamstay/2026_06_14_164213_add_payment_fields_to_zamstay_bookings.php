<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('zamstay_bookings', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('special_requests');
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->string('payment_phone')->nullable()->after('payment_reference');
            $table->string('transaction_id')->nullable()->after('payment_phone');
            $table->timestamp('paid_at')->nullable()->after('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('zamstay_bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_reference', 'payment_phone', 'transaction_id', 'paid_at']);
        });
    }
};
