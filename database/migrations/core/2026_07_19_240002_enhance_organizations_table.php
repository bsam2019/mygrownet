<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('country')->nullable()->after('type');
            $table->string('currency')->nullable()->after('country');
            $table->string('timezone')->nullable()->after('currency');
            $table->string('language')->default('en')->after('timezone');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['country', 'currency', 'timezone', 'language']);
        });
    }
};
