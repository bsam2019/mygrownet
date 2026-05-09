<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cms_payments')) {
            if (!Schema::hasColumn('cms_payments', 'is_voided')) {
                Schema::table('cms_payments', function (Blueprint $table) {
                    $table->boolean('is_voided')->default(false)->after('notes');
                });
            }
            if (!Schema::hasColumn('cms_payments', 'void_reason')) {
                Schema::table('cms_payments', function (Blueprint $table) {
                    $table->string('void_reason')->nullable()->after('is_voided');
                });
            }
            if (!Schema::hasColumn('cms_payments', 'voided_at')) {
                Schema::table('cms_payments', function (Blueprint $table) {
                    $table->timestamp('voided_at')->nullable()->after('void_reason');
                });
            }
            if (!Schema::hasColumn('cms_payments', 'voided_by')) {
                Schema::table('cms_payments', function (Blueprint $table) {
                    $table->unsignedBigInteger('voided_by')->nullable()->after('voided_at');
                    $table->foreign('voided_by')->references('id')->on('cms_users')->onDelete('set null');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('cms_payments', function (Blueprint $table) {
            $table->dropForeign(['voided_by']);
            $table->dropColumn(['is_voided', 'void_reason', 'voided_at', 'voided_by']);
        });
    }
};
