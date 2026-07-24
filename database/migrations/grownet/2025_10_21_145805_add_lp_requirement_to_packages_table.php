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
        Schema::table('packages', function (Blueprint $table) {
            $table->integer('lp_requirement')->default(0)->after('price');
        });

        // Set default LP requirements for each level
        DB::table('packages')->where('slug', 'associate-monthly')->update(['lp_requirement' => 0]);
        DB::table('packages')->where('slug', 'professional-monthly')->update(['lp_requirement' => 500]);
        DB::table('packages')->where('slug', 'senior-monthly')->update(['lp_requirement' => 1500]);
        DB::table('packages')->where('slug', 'manager-monthly')->update(['lp_requirement' => 3000]);
        DB::table('packages')->where('slug', 'director-monthly')->update(['lp_requirement' => 5000]);
        DB::table('packages')->where('slug', 'executive-monthly')->update(['lp_requirement' => 8000]);
        DB::table('packages')->where('slug', 'ambassador-monthly')->update(['lp_requirement' => 12000]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('lp_requirement');
        });
    }
};
