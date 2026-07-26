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
        Schema::table('withdrawals', function (Blueprint $table) {
            if (!Schema::hasColumn('withdrawals', 'phone_number')) {
                $table->string('phone_number')->nullable()->after('wallet_address');
            }
            if (!Schema::hasColumn('withdrawals', 'reference')) {
                $table->string('reference')->nullable()->unique()->after('phone_number');
            }
            if (!Schema::hasColumn('withdrawals', 'account_name')) {
                $table->string('account_name')->nullable()->after('reference');
            }
            if (!Schema::hasColumn('withdrawals', 'notes')) {
                $table->text('notes')->nullable()->after('account_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'reference', 'account_name', 'notes']);
        });
    }
};
