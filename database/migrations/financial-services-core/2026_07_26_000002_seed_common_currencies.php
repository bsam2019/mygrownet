<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('currencies')->insertOrIgnore([
            ['code' => 'ZMW', 'name' => 'Zambian Kwacha', 'symbol' => 'ZK', 'decimal_places' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'USD', 'name' => 'United States Dollar', 'symbol' => '$', 'decimal_places' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R', 'decimal_places' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'GBP', 'name' => 'British Pound Sterling', 'symbol' => '£', 'decimal_places' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'decimal_places' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        DB::table('currencies')->whereIn('code', ['ZMW', 'USD', 'ZAR', 'GBP', 'EUR'])->delete();
    }
};
