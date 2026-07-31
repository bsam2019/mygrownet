<?php

namespace Database\Seeders;

use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\Domain;
use Illuminate\Database\Seeder;

class ApplicationDomainsSeeder extends Seeder
{
    /**
     * Seed the domains table with application subdomains.
     * This tells the system which subdomain maps to which application.
     */
    public function run(): void
    {
        $appSubdomains = [
            'grownet' => 'grownet.mygrownet.com',
            'zamstay' => 'zamstay.mygrownet.com',
            'growmart' => 'growmart.mygrownet.com',
            'lifeplus' => 'lifeplus.mygrownet.com',
            'primeedge' => 'primeedge.mygrownet.com',
            'growbuilder' => 'growbuilder.mygrownet.com',
            'bms' => 'bms.mygrownet.com',
            'stockflow' => 'stockflow.mygrownet.com',
            'growfinance' => 'growfinance.mygrownet.com',
            'bizdocs' => 'bizdocs.mygrownet.com',
            'bizboost' => 'bizboost.mygrownet.com',
            'growstorage' => 'growstorage.mygrownet.com',
        ];

        foreach ($appSubdomains as $slug => $domainName) {
            $app = Application::where('slug', $slug)->first();
            
            if (!$app) {
                $this->command->warn("Application '{$slug}' not found, skipping domain '{$domainName}'");
                continue;
            }

            Domain::updateOrCreate(
                ['domain' => $domainName],
                [
                    'type' => 'application',
                    'application_id' => $app->id,
                    'organization_id' => null,
                    'is_active' => true,
                ]
            );

            $this->command->info("✓ Registered domain '{$domainName}' for {$app->name}");
        }

        // Register platform domain
        Domain::updateOrCreate(
            ['domain' => 'mygrownet.com'],
            [
                'type' => 'platform',
                'application_id' => null,
                'organization_id' => null,
                'is_active' => true,
            ]
        );

        $this->command->info("✓ Registered platform domain 'mygrownet.com'");
        $this->command->info("Registered " . count($appSubdomains) . " application domains + 1 platform domain.");
    }
}
