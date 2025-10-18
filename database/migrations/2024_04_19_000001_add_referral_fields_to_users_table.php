<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'referrer_id')) {
                $table->foreignId('referrer_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'referral_code')) {
                $table->string('referral_code')->unique()->nullable();
            }
            if (!Schema::hasColumn('users', 'total_referral_earnings')) {
                $table->decimal('total_referral_earnings', 15, 2)->default(0.00);
            }
            if (!Schema::hasColumn('users', 'referral_count')) {
                $table->integer('referral_count')->default(0);
            }
            if (!Schema::hasColumn('users', 'last_referral_at')) {
                $table->timestamp('last_referral_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referrer_id']);
            $table->dropColumn([
                'referrer_id',
                'referral_code',
                'total_referral_earnings',
                'referral_count',
                'last_referral_at'
            ]);
        });
    }
}; 