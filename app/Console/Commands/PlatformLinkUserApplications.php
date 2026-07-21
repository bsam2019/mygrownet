<?php

namespace App\Console\Commands;

use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\UserApplication;
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
