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
        Schema::table('investor_accounts', function (Blueprint $table) {
            $table->decimal('equity_percentage', 6, 4)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investor_accounts', function (Blueprint $table) {
            $table->decimal('equity_percentage', 5, 4)->default(0)->change();
        });
    }
};
