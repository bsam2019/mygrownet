<?php

namespace App\Console\Commands;

use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\UserApplication;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaUserModel;
use App\Models\User;
use Illuminate\Console\Command;

class PlatformLinkUserApplications extends Command
{
    protected $signature = 'platform:link-user-applications
        {--dry-run : Preview changes without writing to database}
    ';

    protected $description = 'Link existing users across all auth systems to their applications';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $linked = 0;
        $skipped = 0;

        $stockflow = Application::where('slug', 'stockflow')->first();
        $primeedge = Application::where('slug', 'primeedge')->first();

        // Main site users → web guard applications (role-based)
        $this->newLine();
        $this->info('Linking main site users...');

        User::each(function (User $user) use ($dryRun, &$linked, &$skipped) {
            $apps = $this->resolveMainSiteApplications($user);
            foreach ($apps as $appSlug => $relation) {
                if (!$dryRun) {
                    UserApplication::firstOrCreate([
                        'user_id' => $user->id,
                        'application_id' => $this->getAppId($appSlug),
                    ], [
                        'relationship_type' => $relation,
                        'status' => 'active',
                    ]);
                }
                $linked++;
            }
            if (empty($apps)) {
                $skipped++;
            }
        });

        // StockFlow users
        $this->newLine();
        $this->info('Linking StockFlow users...');
        if ($stockflow) {
            SaUserModel::each(function (SaUserModel $saUser) use ($dryRun, $stockflow, &$linked) {
                // Find or create a main user record for cross-reference
                $user = User::where('email', $saUser->email)->first();
                if ($user && !$dryRun) {
                    UserApplication::firstOrCreate([
                        'user_id' => $user->id,
                        'application_id' => $stockflow->id,
                    ], [
                        'relationship_type' => $saUser->is_stockflow_admin ? 'admin' : 'member',
                        'status' => 'active',
                    ]);
                }
                $linked++;
            });
        }

        // PrimeEdge users
        $this->newLine();
        $this->info('Linking PrimeEdge clients...');
        if ($primeedge) {
            $clientModel = $primeedge->model ?? \App\Infrastructure\PrimeEdge\Persistence\ClientModel::class;
            $clientModel::each(function ($client) use ($dryRun, $primeedge, &$linked) {
                $user = User::where('email', $client->email)->first();
                if ($user && !$dryRun) {
                    UserApplication::firstOrCreate([
                        'user_id' => $user->id,
                        'application_id' => $primeedge->id,
                    ], [
                        'relationship_type' => 'customer',
                        'status' => 'active',
                    ]);
                }
                $linked++;
            });
        }

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Links created/validated', $linked],
                ['Main site users without app (skipped)', $skipped],
            ]
        );

        return static::SUCCESS;
    }

    private function resolveMainSiteApplications(User $user): array
    {
        $apps = [];
        if (app()->runningInConsole() && !$user->relationLoaded('roles')) {
            $user->load('roles');
        }

        $roleSlugs = $user->roles->pluck('slug')->toArray();

        if ($user->hasRole('Administrator') || $user->hasRole('admin')) {
            $apps['grownet'] = 'admin';
        }
        if ($user->hasRole('Member') || $user->hasRole('Investor')) {
            $apps['grownet'] = $apps['grownet'] ?? 'member';
        }
        if ($user->hasRole('Investment Manager')) {
            $apps['grownet'] = 'manager';
        }
        if ($user->hasRole('Support Agent')) {
            $apps['grownet'] = 'support';
        }

        if (!empty(array_intersect($roleSlugs, ['stockflow-manager', 'stockflow-viewer', 'stockflow-auditor']))) {
            $apps['stockflow'] = 'member';
        }
        if (!empty(array_intersect($roleSlugs, ['growfinance-accountant', 'growfinance-viewer']))) {
            $apps['growfinance'] = 'member';
        }
        if (!empty(array_intersect($roleSlugs, ['bizdocs-editor', 'bizdocs-viewer']))) {
            $apps['bizdocs'] = 'member';
        }

        return $apps;
    }

    private function getAppId(string $slug): ?int
    {
        static $ids = [];
        if (!isset($ids[$slug])) {
            $ids[$slug] = Application::where('slug', $slug)->value('id');
        }
        return $ids[$slug];
    }
}
