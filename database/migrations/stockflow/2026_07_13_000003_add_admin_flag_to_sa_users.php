<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sa_users', 'is_stockflow_admin')) {
            Schema::table('sa_users', function (Blueprint $table) {
                $table->boolean('is_stockflow_admin')->default(false)->after('password');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sa_users', 'is_stockflow_admin')) {
            Schema::table('sa_users', function (Blueprint $table) {
                $table->dropColumn('is_stockflow_admin');
            });
        }
    }
};
