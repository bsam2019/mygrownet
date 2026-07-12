<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('growmart_orders', function (Blueprint $table) {
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->string('payment_phone')->nullable()->after('payment_reference');
            $table->text('payment_notes')->nullable()->after('payment_phone');
            $table->timestamp('payment_submitted_at')->nullable()->after('payment_notes');
        });
    }

    public function down(): void
    {
        Schema::table('growmart_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_reference', 'payment_phone', 'payment_notes', 'payment_submitted_at']);
        });
    }
};
