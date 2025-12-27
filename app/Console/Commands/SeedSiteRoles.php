<?php

namespace App\Console\Commands;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Services\GrowBuilder\SiteRoleService;
use Database\Seeders\SitePermissionsSeeder;
use Illuminate\Console\Command;

class SeedSiteRoles extends Command
{
    protected $signature = 'growbuilder:seed-roles 
                            {--site= : Specific site ID to seed roles for}
                            {--permissions : Also seed global permissions}
                            {--force : Force re-creation of roles even if they exist}';
    
    protected $description = 'Seed default roles for GrowBuilder sites';

    public function handle(SiteRoleService $roleService): int
    {
        // Seed permissions first if requested or if none exist
        if ($this->option('permissions') || \App\Infrastructure\GrowBuilder\Models\SitePermission::count() === 0) {
            $this->info('Seeding global permissions...');
            $seeder = new SitePermissionsSeeder();
            $seeder->setCommand($this);
            $seeder->run();
        }

        $siteId = $this->option('site');
        $force = $this->option('force');

        if ($siteId) {
            $sites = GrowBuilderSite::where('id', $siteId)->get();
        } else {
            $sites = GrowBuilderSite::all();
        }

        if ($sites->isEmpty()) {
            $this->error('No sites found.');
            return 1;
        }

        foreach ($sites as $site) {
            $existingRoles = $site->siteRoles()->count();
            
            if ($existingRoles > 0 && !$force) {
                $this->info("Site '{$site->name}' (ID: {$site->id}) already has {$existingRoles} roles. Use --force to recreate.");
                continue;
            }

            if ($force && $existingRoles > 0) {
                $this->warn("Deleting existing roles for site '{$site->name}'...");
                $site->siteRoles()->delete();
            }

            $this->info("Creating roles for site '{$site->name}' (ID: {$site->id})...");
            $roles = $roleService->createDefaultRolesForSite($site);
            $this->info("  Created " . count($roles) . " roles.");
        }

        $this->info('Done!');
        return 0;
    }
}
