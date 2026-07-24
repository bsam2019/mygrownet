<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_payments', function (Blueprint $table) {
            $table->decimal('unallocated_amount', 15, 2)->default(0)->after('amount');
            $table->boolean('is_overpayment')->default(false)->after('unallocated_amount');
        });

        Schema::table('cms_customers', function (Blueprint $table) {
            $table->decimal('credit_balance', 15, 2)->default(0)->after('outstanding_balance');
            $table->text('credit_notes')->nullable()->after('credit_balance');
        });
    }

    public function down(): void
    {
        Schema::table('cms_payments', function (Blueprint $table) {
            $table->dropColumn(['unallocated_amount', 'is_overpayment']);
        });

        Schema::table('cms_customers', function (Blueprint $table) {
            $table->dropColumn(['credit_balance', 'credit_notes']);
        });
    }
};
