<?php

namespace App\Console\Commands;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorPlatform;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\PlatformQuota;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SeedCreatorPlatformCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'growstream:seed-creator-platform {user_id? : Optional User ID to associate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed a sample Creator Platform (e.g. Acme Online Tuition Academy) with private portal content';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = $userId ? User::find($userId) : User::first();

        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Dr. Mutale (Online Tuitions)',
                'email' => 'tutor@acmetuitions.com',
                'account_types' => ['member', 'business'],
            ]);
        }

        $orgId = $user->organization_id ?? $user->id;

        // 1. Create or update Creator Profile
        $creator = CreatorProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'channel_name' => 'Acme Online Tuition Academy',
                'channel_slug' => 'acme-tuitions',
                'display_name' => 'Dr. Mutale',
                'bio' => 'Premium Mathematics & Physics Online Tuitions for High School & College',
                'status' => 'approved',
                'is_verified' => true,
            ]
        );

        // 2. Create Creator Platform Settings
        $platform = CreatorPlatform::updateOrCreate(
            ['organization_id' => $orgId],
            [
                'brand_name' => 'Acme Online Tuition Academy',
                'subdomain' => 'acmetuitions',
                'custom_domain' => 'www.acmetuitions.com',
                'brand_color' => '#e2571f',
                'is_active' => true,
            ]
        );

        // 3. Create Platform Quota
        PlatformQuota::updateOrCreate(
            ['organization_id' => $orgId],
            [
                'storage_minutes_limit' => 2500,
                'delivery_gb_limit' => 250,
                'current_storage_minutes' => 140,
                'current_delivery_gb' => 22,
            ]
        );

        // 4. Create Private Tuition Video
        Video::updateOrCreate(
            ['slug' => 'acme-maths-calculus-masterclass'],
            [
                'uuid' => (string) Str::uuid(),
                'title' => 'Calculus & Algebra Masterclass - Lesson 1',
                'description' => 'Comprehensive private tuition lecture on derivatives and integrals.',
                'creator_id' => $creator->id,
                'organization_id' => $orgId,
                'publishing_destination' => 'portal', // Private to tuition portal only!
                'access_level' => 'premium',
                'duration' => 2700, // 45 mins
                'is_published' => true,
                'published_at' => now(),
                'chapters' => [
                    ['title' => 'Introduction & Formula Review', 'timestamp' => 0],
                    ['title' => 'Derivatives Concept', 'timestamp' => 300],
                    ['title' => 'Worked Examples', 'timestamp' => 1200],
                    ['title' => 'Q&A & Practice Exercises', 'timestamp' => 2100],
                ],
            ]
        );

        $this->info("Sample Creator Platform successfully seeded!");
        $this->table(
            ['Attribute', 'Value'],
            [
                ['Platform Brand', $platform->brand_name],
                ['Subdomain', $platform->subdomain . '.growstream.app'],
                ['Custom Domain', $platform->custom_domain],
                ['Tutor/User', $user->name . ' (' . $user->email . ')'],
                ['Sample Video Destination', 'portal (Private to platform)'],
            ]
        );

        return 0;
    }
}
