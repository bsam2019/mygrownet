<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'starter_kit_shop_credit')) {
                $table->decimal('starter_kit_shop_credit', 10, 2)->default(0.00)->after('email_verified_at');
            }
            if (!Schema::hasColumn('users', 'starter_kit_credit_expiry')) {
                $table->date('starter_kit_credit_expiry')->nullable()->after('starter_kit_shop_credit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'starter_kit_shop_credit')) {
                $table->dropColumn('starter_kit_shop_credit');
            }
            if (Schema::hasColumn('users', 'starter_kit_credit_expiry')) {
                $table->dropColumn('starter_kit_credit_expiry');
            }
        });
    }
};
