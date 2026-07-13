<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StockFlowAdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sa_users')->updateOrInsert(
            ['email' => 'admin@stockflow.com'],
            [
                'name' => 'StockFlow Super Admin',
                'password' => Hash::make('StockFlow@2026!'),
                'is_stockflow_admin' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info('StockFlow admin user seeded: admin@stockflow.com / StockFlow@2026!');
    }
}
