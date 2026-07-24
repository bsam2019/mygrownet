<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('starter_kit_purchases', function (Blueprint $table) {
            $table->enum('tier', ['basic', 'premium'])->default('basic')->after('user_id');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->enum('starter_kit_tier', ['basic', 'premium'])->nullable()->after('has_starter_kit');
        });
    }

    public function down(): void
    {
        Schema::table('starter_kit_purchases', function (Blueprint $table) {
            $table->dropColumn('tier');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('starter_kit_tier');
        });
    }
};

