<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_companies', function (Blueprint $table) {
            $table->boolean('has_operations_module')->default(false)->after('has_bizdocs_module');
        });
    }

    public function down(): void
    {
        Schema::table('cms_companies', function (Blueprint $table) {
            $table->dropColumn('has_operations_module');
        });
    }
};
