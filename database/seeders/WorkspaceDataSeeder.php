<?php

namespace Database\Seeders;

use App\Domain\Core\Models\Application;
use App\Domain\Core\Models\ApplicationInstallation;
use App\Domain\Core\Models\Organization;
use App\Domain\Core\Models\OrganizationMember;
use App\Domain\Core\Models\UserApplicationSubscription;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;

class WorkspaceDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. User Profiles — backfill for all users
        $userCount = User::count();
        $created = 0;
        foreach (User::cursor() as $user) {
            if (!UserProfile::where('user_id', $user->id)->exists()) {
                $parts = explode(' ', $user->name, 2);
                UserProfile::create([
                    'user_id' => $user->id,
                    'first_name' => $parts[0] ?? $user->name,
                    'last_name' => $parts[1] ?? '',
                    'timezone' => 'Africa/Lusaka',
                    'language' => 'en',
                    'country' => 'Zambia',
                ]);
                $created++;
            }
        }
        $this->command->info("Created {$created} user profiles.");

        // 2. Organization Members — from cms_users where user+org exist
        $memberCount = 0;
        if (\Schema::hasTable('cms_users')) {
            $cmsUsers = \DB::table('cms_users')->where('status', 'active')->get();
            foreach ($cmsUsers as $cu) {
                $org = Organization::find($cu->company_id ?? $cu->organization_id);
                if (!$org) continue;

                $exists = OrganizationMember::where('organization_id', $org->id)
                    ->where('user_id', $cu->user_id)
                    ->exists();

                if (!$exists) {
                    OrganizationMember::create([
                        'organization_id' => $org->id,
                        'user_id' => $cu->user_id,
                        'role' => 'owner',
                        'status' => 'active',
                        'permissions' => ['*'],
                        'joined_at' => $cu->created_at ?? now(),
                    ]);
                    $memberCount++;
                }
            }
        }
        $this->command->info("Created {$memberCount} organization members.");

        // 3. Application Installations — for all business apps on orgs
        $instCount = 0;
        $businessApps = Application::where('category', 'business')
            ->where('lifecycle', 'active')
            ->pluck('id');

        foreach (Organization::cursor() as $org) {
            foreach ($businessApps as $appId) {
                $exists = ApplicationInstallation::where('organization_id', $org->id)
                    ->where('application_id', $appId)
                    ->exists();
                if (!$exists) {
                    ApplicationInstallation::create([
                        'organization_id' => $org->id,
                        'application_id' => $appId,
                        'status' => 'active',
                        'settings' => null,
                    ]);
                    $instCount++;
                }
            }
        }
        $this->command->info("Created {$instCount} application installations.");

        // 4. User App Subscriptions — default free consumer apps for all users
        $subCount = 0;
        $freeConsumerApps = Application::where('category', 'consumer')
            ->where('subscription_required', false)
            ->where('lifecycle', 'active')
            ->pluck('id');

        foreach (User::cursor() as $user) {
            foreach ($freeConsumerApps as $appId) {
                $exists = UserApplicationSubscription::where('user_id', $user->id)
                    ->where('application_id', $appId)
                    ->exists();
                if (!$exists) {
                    UserApplicationSubscription::create([
                        'user_id' => $user->id,
                        'application_id' => $appId,
                        'status' => 'active',
                        'expires_at' => null,
                    ]);
                    $subCount++;
                }
            }
        }
        $this->command->info("Created {$subCount} user app subscriptions.");
    }
}
