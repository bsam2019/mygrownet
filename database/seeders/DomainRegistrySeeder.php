<?php

namespace Database\Seeders;

use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\Domain;
use Illuminate\Database\Seeder;

class DomainRegistrySeeder extends Seeder
{
    public function run(): void
    {
        // Platform domain
        Domain::firstOrCreate(
            ['domain' => 'mygrownet.com'],
            ['type' => 'platform', 'is_active' => true],
        );

        // Application subdomains — resolve app IDs by slug
        $subdomains = [
            ['slug' => 'grownet',    'domain' => 'grownet.mygrownet.com'],
            ['slug' => 'bizboost',   'domain' => 'bizboost.mygrownet.com'],
            ['slug' => 'growfinance','domain' => 'finance.mygrownet.com'],
            ['slug' => 'growbuilder','domain' => 'growbuilder.mygrownet.com'],
            ['slug' => 'growmart',   'domain' => 'growmart.mygrownet.com'],
            ['slug' => 'stockflow',  'domain' => 'stockflow.mygrownet.com'],
            ['slug' => 'bms',        'domain' => 'bms.mygrownet.com'],
            ['slug' => 'zamstay',    'domain' => 'zamstay.mygrownet.com'],
            ['slug' => 'primeedge',  'domain' => 'primeedge.mygrownet.com'],
            ['slug' => 'bizdocs',    'domain' => 'bizdocs.mygrownet.com'],
            ['slug' => 'growstorage','domain' => 'growstorage.mygrownet.com'],
        ];

        foreach ($subdomains as $entry) {
            $app = Application::where('slug', $entry['slug'])->first();
            if (!$app) {
                continue;
            }

            Domain::firstOrCreate(
                ['domain' => $entry['domain']],
                [
                    'type' => 'application',
                    'application_id' => $app->id,
                    'is_active' => true,
                ],
            );
        }
    }
}
