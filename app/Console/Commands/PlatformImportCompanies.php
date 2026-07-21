<?php

namespace App\Console\Commands;

use App\Domain\Core\Models\Organization;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BMS\CompanyModel as CmsCompany;
use App\Models\StockAudit\Company as StockFlowCompany;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PlatformImportCompanies extends Command
{
    protected $signature = 'platform:import-companies
        {--owner= : Default user ID to assign as organization owner for tables without a direct user reference}
        {--dry-run : Preview changes without writing to database}
    ';

    protected $description = 'Import existing company tables into Core organizations';

    private int $created = 0;
    private int $skipped = 0;
    private int $linked = 0;

    public function handle(): int
    {
        $defaultOwnerId = $this->option('owner');
        if ($defaultOwnerId && !User::find($defaultOwnerId)) {
            $this->error("User ID {$defaultOwnerId} not found.");
            return static::FAILURE;
        }

        if (!$defaultOwnerId) {
            $defaultOwnerId = User::orderBy('id')->value('id');
            if (!$defaultOwnerId) {
                $this->error('No users found. Create a user first or specify --owner.');
                return static::FAILURE;
            }
            $this->warn("Using user ID {$defaultOwnerId} as default owner.");
        }

        $this->importStockFlowCompanies((int) $defaultOwnerId);
        $this->importCmsCompanies((int) $defaultOwnerId);
        $this->importBizBoostBusinesses();

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Organizations created', $this->created],
                ['Already linked (skipped)', $this->skipped],
                ['Foreign keys updated', $this->linked],
            ]
        );

        return static::SUCCESS;
    }

    private function importStockFlowCompanies(int $defaultOwnerId): void
    {
        $this->newLine();
        $this->info('Processing StockFlow companies (sa_companies)...');

        StockFlowCompany::query()
            ->orderBy('id')
            ->each(function (StockFlowCompany $company) use ($defaultOwnerId) {
                if ($company->organization_id) {
                    $this->skipped++;
                    return;
                }

                $org = $this->createOrganization(
                    name: $company->name,
                    slug: $company->subdomain ?: Str::slug($company->name),
                    ownerId: $defaultOwnerId,
                    status: $company->status === 'active' ? 'active' : 'suspended',
                );

                if ($org) {
                    $company->organization_id = $org->id;
                    $company->save();
                    $this->linked++;
                }
            });
    }

    private function importCmsCompanies(int $defaultOwnerId): void
    {
        $this->newLine();
        $this->info('Processing CMS companies (cms_companies)...');

        CmsCompany::query()
            ->orderBy('id')
            ->each(function (CmsCompany $company) use ($defaultOwnerId) {
                if ($company->organization_id) {
                    $this->skipped++;
                    return;
                }

                $org = $this->createOrganization(
                    name: $company->name,
                    slug: Str::slug($company->name),
                    ownerId: $defaultOwnerId,
                    status: $company->status === 'active' ? 'active' : 'suspended',
                );

                if ($org) {
                    $company->organization_id = $org->id;
                    $company->save();
                    $this->linked++;
                }
            });
    }

    private function importBizBoostBusinesses(): void
    {
        $this->newLine();
        $this->info('Processing BizBoost businesses (bizboost_businesses)...');

        BizBoostBusinessModel::query()
            ->orderBy('id')
            ->each(function (BizBoostBusinessModel $business) {
                if ($business->organization_id) {
                    $this->skipped++;
                    return;
                }

                $ownerId = $business->user_id;
                if (!User::find($ownerId)) {
                    $this->warn("  Skipping business #{$business->id} — owner user #{$ownerId} not found.");
                    $this->skipped++;
                    return;
                }

                $org = $this->createOrganization(
                    name: $business->name,
                    slug: $business->slug ?: Str::slug($business->name),
                    ownerId: (int) $ownerId,
                    status: $business->is_active ? 'active' : 'suspended',
                );

                if ($org) {
                    $business->organization_id = $org->id;
                    $business->save();
                    $this->linked++;
                }
            });
    }

    private function createOrganization(string $name, string $slug, int $ownerId, string $status): ?Organization
    {
        $baseSlug = $slug ?: Str::slug($name);
        $uniqueSlug = $baseSlug;
        $suffix = 1;

        while (Organization::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $baseSlug . '-' . $suffix++;
        }

        if ($this->option('dry-run')) {
            $this->line("  [DRY-RUN] Would create: {$name} ({$uniqueSlug})");
            return null;
        }

        $org = Organization::create([
            'uuid' => Str::uuid(),
            'name' => $name,
            'slug' => $uniqueSlug,
            'type' => 'company',
            'status' => $status,
            'owner_id' => $ownerId,
        ]);

        $this->created++;
        $this->line("  Created organization #{$org->id}: {$name} ({$uniqueSlug})");

        return $org;
    }
}
