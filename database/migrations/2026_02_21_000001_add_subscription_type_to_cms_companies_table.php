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
        if (Schema::hasTable('cms_companies')) {
            if (!Schema::hasColumn('cms_companies', 'subscription_type')) {
                Schema::table('cms_companies', function (Blueprint $table) {
                    $table->string('subscription_type')->default('paid')->after('status');
                });
            }
            if (!Schema::hasColumn('cms_companies', 'sponsor_reference')) {
                Schema::table('cms_companies', function (Blueprint $table) {
                    $table->string('sponsor_reference')->nullable()->after('subscription_type');
                });
            }
            if (!Schema::hasColumn('cms_companies', 'subscription_notes')) {
                Schema::table('cms_companies', function (Blueprint $table) {
                    $table->text('subscription_notes')->nullable()->after('sponsor_reference');
                });
            }
            if (!Schema::hasColumn('cms_companies', 'complimentary_until')) {
                Schema::table('cms_companies', function (Blueprint $table) {
                    $table->timestamp('complimentary_until')->nullable()->after('subscription_notes');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_companies', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_type',
                'sponsor_reference',
                'subscription_notes',
                'complimentary_until',
            ]);
        });
    }
};
