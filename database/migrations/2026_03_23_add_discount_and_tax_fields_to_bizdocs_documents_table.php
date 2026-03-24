<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bizdocs_documents', function (Blueprint $table) {
            $table->string('discount_type')->default('amount')->after('discount_total'); // 'amount' or 'percentage'
            $table->decimal('discount_value', 10, 2)->default(0)->after('discount_type'); // The input value
            $table->boolean('collect_tax')->default(true)->after('discount_value'); // Whether to collect tax
        });
    }

    public function down(): void
    {
        Schema::table('bizdocs_documents', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value', 'collect_tax']);
        });
    }
};
