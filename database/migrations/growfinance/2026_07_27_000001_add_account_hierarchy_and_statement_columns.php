<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('growfinance_accounts', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()
                ->after('id')
                ->constrained('growfinance_accounts')
                ->nullOnDelete();

            $table->unsignedTinyInteger('level')->default(0)->after('parent_id');

            $table->string('path', 500)->nullable()->after('level');

            $table->string('statement_category', 50)->nullable()->after('description');

            $table->enum('normal_balance', ['debit', 'credit'])->after('code');
        });
    }

    public function down(): void
    {
        Schema::table('growfinance_accounts', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'level', 'path', 'statement_category', 'normal_balance']);
        });
    }
};
