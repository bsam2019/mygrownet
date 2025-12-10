<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixStuckMigrationSeeder extends Seeder
{
    /**
     * Fix stuck migrations where table exists but migration not recorded.
     */
    public function run(): void
    {
        $stuckMigrations = [
            '2025_12_04_200009_create_bizboost_integrations_table' => 'bizboost_integrations',
        ];

        foreach ($stuckMigrations as $migration => $table) {
            if (Schema::hasTable($table)) {
                $exists = DB::table('migrations')
                    ->where('migration', $migration)
                    ->exists();

                if (!$exists) {
                    $batch = DB::table('migrations')->max('batch') ?? 0;
                    DB::table('migrations')->insert([
                        'migration' => $migration,
                        'batch' => $batch + 1,
                    ]);
                    $this->command->info("Fixed stuck migration: {$migration}");
                }
            }
        }
    }
}
