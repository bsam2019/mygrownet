<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('investments', function (Blueprint $table) {
            if (!Schema::hasColumn('investments', 'next_payment_date')) {
                $table->timestamp('next_payment_date')->nullable()->after('status');
            }
        });
    }

    public function down()
    {
        Schema::table('investments', function (Blueprint $table) {
            if (Schema::hasColumn('investments', 'next_payment_date')) {
                $table->dropColumn('next_payment_date');
            }
        });
    }
}; 