<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'default_company_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('default_company_id')->nullable()->after('preferred_currency');

                $table->foreign('default_company_id')
                    ->references('id')
                    ->on('cms_companies')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['default_company_id']);
            $table->dropColumn('default_company_id');
        });
    }
};
