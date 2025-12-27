<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_roles', function (Blueprint $table) {
            $table->enum('type', ['staff', 'client'])->default('client')->after('level');
            $table->string('icon', 50)->nullable()->after('type');
            $table->string('color', 20)->nullable()->after('icon');
        });

        // Update existing roles with appropriate types
        DB::table('site_roles')->where('slug', 'admin')->update(['type' => 'staff', 'icon' => 'shield-check', 'color' => 'indigo']);
        DB::table('site_roles')->where('slug', 'editor')->update(['type' => 'staff', 'icon' => 'pencil-square', 'color' => 'blue']);
        DB::table('site_roles')->where('slug', 'member')->update(['type' => 'client', 'icon' => 'user', 'color' => 'emerald']);
        DB::table('site_roles')->where('slug', 'customer')->update(['type' => 'client', 'icon' => 'shopping-bag', 'color' => 'amber']);
    }

    public function down(): void
    {
        Schema::table('site_roles', function (Blueprint $table) {
            $table->dropColumn(['type', 'icon', 'color']);
        });
    }
};
