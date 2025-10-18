<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('investment_metrics', function (Blueprint $table) {
            $table->index('date');
            $table->index(['date', 'total_value']);
            $table->index(['date', 'average_roi']);
        });
    }

    public function down()
    {
        Schema::table('investment_metrics', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['date', 'total_value']);
            $table->dropIndex(['date', 'average_roi']);
        });
    }
};