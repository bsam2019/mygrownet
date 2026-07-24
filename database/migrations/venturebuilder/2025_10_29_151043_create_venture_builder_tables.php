<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventures', function (Blueprint $table) {
            $table->decimal('share_price', 15, 2)->nullable()->after('maximum_investment')
                ->comment('Price per share - if null, calculated as K100/share by default');
        });

        Schema::table('venture_investments', function (Blueprint $table) {
            $table->index(['user_id', 'venture_id', 'status'], 'inv_user_venture_status_idx');
        });

        Schema::table('venture_shareholders', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'sh_user_status_idx');
        });

        Schema::table('venture_dividends', function (Blueprint $table) {
            $table->index(['shareholder_id', 'status'], 'div_shareholder_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ventures', function (Blueprint $table) {
            $table->dropColumn('share_price');
        });

        Schema::table('venture_investments', function (Blueprint $table) {
            $table->dropIndex('inv_user_venture_status_idx');
        });

        Schema::table('venture_shareholders', function (Blueprint $table) {
            $table->dropIndex('sh_user_status_idx');
        });

        Schema::table('venture_dividends', function (Blueprint $table) {
            $table->dropIndex('div_shareholder_status_idx');
        });
    }
};
