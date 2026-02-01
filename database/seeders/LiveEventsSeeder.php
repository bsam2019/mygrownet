<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LiveEventsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $events = [
            [
                'title' => 'MyGrowNet Platform Orientation',
                'slug' => 'platform-orientation-' . $now->format('Y-m'),
                'description' => 'Welcome session for new members. Learn how to navigate the platform and get started.',
                'event_type' => 'webinar',
                'scheduled_at' => $now->copy()->addDays(3)->setTime(18, 0, 0),
                'duration_minutes' => 60,
                'meeting_link' => 'https://zoom.us/j/example-orientation',
                'meeting_id' => 'TBD',
                'meeting_password' => null,
                'max_attendees' => 100,
                'host_name' => 'MyGrowNet Team',
                'is_published' => true,
                'requires_registration' => true,
            ],
            [
                'title' => 'Financial Literacy Workshop',
                'slug' => 'financial-literacy-workshop-' . $now->format('Y-m'),
                'description' => 'Learn essential financial management skills including budgeting, saving, and investing.',
                'event_type' => 'workshop',
                'scheduled_at' => $now->copy()->addDays(7)->setTime(14, 0, 0),
                'duration_minutes' => 90,
                'meeting_link' => 'https://zoom.us/j/example-financial',
                'meeting_id' => 'TBD',
                'meeting_password' => null,
                'max_attendees' => 50,
                'host_name' => 'Finance Team',
                'is_published' => true,
                'requires_registration' => true,
            ],
            [
                'title' => 'Network Building Masterclass',
                'slug' => 'network-building-masterclass-' . $now->format('Y-m'),
                'description' => 'Advanced strategies for growing your team and maximizing your earnings.',
                'event_type' => 'training',
                'scheduled_at' => $now->copy()->addDays(10)->setTime(19, 0, 0),
                'duration_minutes' => 120,
                'meeting_link' => 'https://zoom.us/j/example-network',
                'meeting_id' => 'TBD',
                'meeting_password' => null,
                'max_attendees' => 75,
                'host_name' => 'Success Team',
                'is_published' => true,
                'requires_registration' => true,
            ],
            [
                'title' => 'Monthly Success Stories',
                'slug' => 'success-stories-' . $now->format('Y-m'),
                'description' => 'Hear from top performers about their journey and strategies for success.',
                'event_type' => 'webinar',
                'scheduled_at' => $now->copy()->addDays(14)->setTime(18, 30, 0),
                'duration_minutes' => 60,
                'meeting_link' => 'https://zoom.us/j/example-success',
                'meeting_id' => 'TBD',
                'meeting_password' => null,
                'max_attendees' => 200,
                'host_name' => 'Community Team',
                'is_published' => true,
                'requires_registration' => true,
            ],
            [
                'title' => 'Q&A with Leadership',
                'slug' => 'qa-with-leadership-' . $now->format('Y-m'),
                'description' => 'Ask questions directly to the MyGrowNet leadership team.',
                'event_type' => 'meeting',
                'scheduled_at' => $now->copy()->addDays(21)->setTime(17, 0, 0),
                'duration_minutes' => 90,
                'meeting_link' => 'https://zoom.us/j/example-qa',
                'meeting_id' => 'TBD',
                'meeting_password' => null,
                'max_attendees' => 150,
                'host_name' => 'Leadership Team',
                'is_published' => true,
                'requires_registration' => true,
            ],
            [
                'title' => 'Business Plan Development Workshop',
                'slug' => 'business-plan-workshop-' . $now->format('Y-m'),
                'description' => 'Learn how to create a solid business plan using our platform tools.',
                'event_type' => 'workshop',
                'scheduled_at' => $now->copy()->addDays(28)->setTime(15, 0, 0),
                'duration_minutes' => 120,
                'meeting_link' => 'https://zoom.us/j/example-bizplan',
                'meeting_id' => 'TBD',
                'meeting_password' => null,
                'max_attendees' => 40,
                'host_name' => 'Business Development Team',
                'is_published' => true,
                'requires_registration' => true,
            ],
        ];

        foreach ($events as $event) {
            DB::table('live_events')->insert(array_merge($event, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
