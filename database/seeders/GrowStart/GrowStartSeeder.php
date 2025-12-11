<?php

namespace Database\Seeders\GrowStart;

use Illuminate\Database\Seeder;

class GrowStartSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            GrowStartCountriesSeeder::class,
            GrowStartIndustriesSeeder::class,
            GrowStartStagesSeeder::class,
            GrowStartBadgesSeeder::class,
            GrowStartTasksSeeder::class,
        ]);
    }
}
