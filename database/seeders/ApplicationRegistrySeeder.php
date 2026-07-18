<?php

namespace Database\Seeders;

use App\Domain\Core\Models\Application;
use Illuminate\Database\Seeder;

class ApplicationRegistrySeeder extends Seeder
{
    public function run(): void
    {
        $apps = [
            ['name' => 'StockFlow',  'slug' => 'stockflow',  'type' => 'business', 'config' => ['domain_slug' => 'stockflow']],
            ['name' => 'BMS',        'slug' => 'bms',        'type' => 'system',   'config' => ['domain_slug' => 'cms']],
            ['name' => 'GrowMart',   'slug' => 'growmart',   'type' => 'consumer', 'config' => ['domain_slug' => 'growmart']],
            ['name' => 'ZamStay',    'slug' => 'zamstay',    'type' => 'consumer', 'config' => ['domain_slug' => 'zamstay']],
            ['name' => 'GrowNet',    'slug' => 'grownet',    'type' => 'platform', 'config' => ['domain_slug' => 'grownet']],
            ['name' => 'BizDocs',    'slug' => 'bizdocs',    'type' => 'business', 'config' => ['domain_slug' => 'bizdocs']],
            ['name' => 'BizBoost',   'slug' => 'bizboost',   'type' => 'business', 'config' => ['domain_slug' => 'bizboost']],
            ['name' => 'GrowBuilder','slug' => 'growbuilder','type' => 'business', 'config' => ['domain_slug' => 'growbuilder']],
            ['name' => 'GrowFinance','slug' => 'growfinance','type' => 'business', 'config' => ['domain_slug' => 'growfinance']],
        ];

        foreach ($apps as $app) {
            Application::firstOrCreate(
                ['slug' => $app['slug']],
                $app
            );
        }
    }
}
