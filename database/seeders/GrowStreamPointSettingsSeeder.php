<?php

namespace Database\Seeders;

use App\Models\LifePointSetting;
use App\Models\BonusPointSetting;
use Illuminate\Database\Seeder;

class GrowStreamPointSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // GrowStream Lifetime Point (LP) Settings
        $lpSettings = [
            [
                'activity_type' => 'growstream_video_watch',
                'name' => 'GrowStream: Watch Video',
                'description' => 'Points awarded when a member starts watching a video',
                'lp_value' => 2,
                'is_active' => true,
            ],
            [
                'activity_type' => 'growstream_video_completion',
                'name' => 'GrowStream: Complete Video',
                'description' => 'Points awarded when a member completes watching a video',
                'lp_value' => 5,
                'is_active' => true,
            ],
            [
                'activity_type' => 'growstream_video_share',
                'name' => 'GrowStream: Share Video',
                'description' => 'Points awarded when a member shares a video',
                'lp_value' => 3,
                'is_active' => true,
            ],
            [
                'activity_type' => 'growstream_subscription',
                'name' => 'GrowStream: Subscribe to Channel',
                'description' => 'Points awarded when a member subscribes to a channel',
                'lp_value' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($lpSettings as $setting) {
            LifePointSetting::updateOrCreate(
                ['activity_type' => $setting['activity_type']],
                $setting
            );
        }

        // GrowStream Bonus Point (BP/MAP) Settings
        $bpSettings = [
            [
                'activity_type' => 'growstream_video_watch',
                'name' => 'GrowStream: Watch Video',
                'description' => 'Monthly activity points awarded when a member starts watching a video',
                'bp_value' => 5,
                'is_active' => true,
            ],
            [
                'activity_type' => 'growstream_video_completion',
                'name' => 'GrowStream: Complete Video',
                'description' => 'Monthly activity points awarded when a member completes watching a video',
                'bp_value' => 10,
                'is_active' => true,
            ],
            [
                'activity_type' => 'growstream_video_share',
                'name' => 'GrowStream: Share Video',
                'description' => 'Monthly activity points awarded when a member shares a video',
                'bp_value' => 8,
                'is_active' => true,
            ],
            [
                'activity_type' => 'growstream_subscription',
                'name' => 'GrowStream: Subscribe to Channel',
                'description' => 'Monthly activity points awarded when a member subscribes to a channel',
                'bp_value' => 20,
                'is_active' => true,
            ],
        ];

        foreach ($bpSettings as $setting) {
            BonusPointSetting::updateOrCreate(
                ['activity_type' => $setting['activity_type']],
                $setting
            );
        }

        $this->command->info('GrowStream point settings seeded successfully!');
    }
}
