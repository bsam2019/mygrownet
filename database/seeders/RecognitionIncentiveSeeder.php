<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IncentiveProgram;
use App\Models\RecognitionEvent;
use App\Models\Achievement;
use App\Models\Leaderboard;
use Carbon\Carbon;

class RecognitionIncentiveSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding recognition and incentive system...');

        // Create incentive programs
        $this->createIncentivePrograms();

        // Create recognition events
        $this->createRecognitionEvents();

        // Create MyGrowNet achievements
        $this->createMyGrowNetAchievements();

        // Create default leaderboards
        $this->createDefaultLeaderboards();

        $this->command->info('Recognition and incentive system seeded successfully!');
    }

    private function createIncentivePrograms(): void
    {
        $programs = [
            [
                'name' => 'Weekly Top Recruiters',
                'slug' => 'weekly-top-recruiters-' . now()->format('Y-W'),
                'description' => 'Top 10 recruiters each week receive cash bonuses and gadgets',
                'type' => 'competition',
                'period_type' => 'weekly',
                'start_date' => now()->startOfWeek(),
                'end_date' => now()->endOfWeek(),
                'eligibility_criteria' => [
                    ['type' => 'referrals', 'weight' => 1.0]
                ],
                'rewards' => [
                    ['position' => 1, 'type' => 'monetary', 'amount' => 5000, 'quantity' => 1, 'description' => 'K5,000 + Smartphone'],
                    ['position' => 2, 'type' => 'monetary', 'amount' => 3000, 'quantity' => 1, 'description' => 'K3,000 + Tablet'],
                    ['position' => 3, 'type' => 'monetary', 'amount' => 2000, 'quantity' => 1, 'description' => 'K2,000 + Gadget'],
                    ['position' => '4-10', 'type' => 'monetary', 'amount' => 1000, 'quantity' => 7, 'description' => 'K1,000 bonus']
                ],
                'max_winners' => 10,
                'is_active' => true,
                'is_recurring' => true,
                'recurrence_pattern' => ['frequency' => 'weekly', 'day' => 'monday'],
                'status' => 'active',
                'total_budget' => 20000,
                'bonus_multipliers' => [
                    'Bronze' => 1.0,
                    'Silver' => 1.2,
                    'Gold' => 1.5,
                    'Diamond' => 1.8,
                    'Elite' => 2.0
                ]
            ],
            [
                'name' => 'Quarterly Raffle Extravaganza',
                'slug' => 'quarterly-raffle-' . now()->format('Y-Q'),
                'description' => 'Quarterly raffle for motorbikes, land plots, and smartphones',
                'type' => 'raffle',
                'period_type' => 'quarterly',
                'start_date' => now()->startOfQuarter(),
                'end_date' => now()->endOfQuarter(),
                'eligibility_criteria' => [
                    ['type' => 'referrals', 'weight' => 2.0],
                    ['type' => 'team_volume', 'weight' => 0.001], // 1 point per K1,000
                    ['type' => 'course_completions', 'weight' => 5.0]
                ],
                'rewards' => [
                    ['type' => 'physical', 'item' => 'motorbike', 'value' => 15000, 'quantity' => 3, 'description' => 'Motorbike'],
                    ['type' => 'physical', 'item' => 'land_plot', 'value' => 25000, 'quantity' => 2, 'description' => 'Land Plot'],
                    ['type' => 'physical', 'item' => 'smartphone', 'value' => 3000, 'quantity' => 10, 'description' => 'Smartphone']
                ],
                'max_winners' => 15,
                'is_active' => true,
                'is_recurring' => true,
                'recurrence_pattern' => ['frequency' => 'quarterly'],
                'status' => 'active',
                'participation_requirements' => [
                    ['type' => 'active_subscription', 'value' => true],
                    ['type' => 'referral_count', 'operator' => '>=', 'value' => 1]
                ]
            ],
            [
                'name' => 'Monthly Achievement Champions',
                'slug' => 'monthly-achievement-champions-' . now()->format('Y-m'),
                'description' => 'Monthly recognition for members with the most achievement points',
                'type' => 'competition',
                'period_type' => 'monthly',
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth(),
                'eligibility_criteria' => [
                    ['type' => 'achievement_points', 'weight' => 1.0]
                ],
                'rewards' => [
                    ['position' => 1, 'type' => 'monetary', 'amount' => 7500, 'quantity' => 1, 'description' => 'K7,500 Achievement Master Bonus'],
                    ['position' => 2, 'type' => 'monetary', 'amount' => 5000, 'quantity' => 1, 'description' => 'K5,000 Achievement Expert Bonus'],
                    ['position' => 3, 'type' => 'monetary', 'amount' => 2500, 'quantity' => 1, 'description' => 'K2,500 Achievement Star Bonus']
                ],
                'max_winners' => 3,
                'is_active' => true,
                'is_recurring' => true,
                'recurrence_pattern' => ['frequency' => 'monthly'],
                'status' => 'active',
                'total_budget' => 15000
            ],
            [
                'name' => 'Profit Boost Week',
                'slug' => 'profit-boost-week-' . now()->addWeek()->format('Y-W'),
                'description' => '25% commission rate increase for one week',
                'type' => 'bonus_multiplier',
                'period_type' => 'weekly',
                'start_date' => now()->addWeek()->startOfWeek(),
                'end_date' => now()->addWeek()->endOfWeek(),
                'eligibility_criteria' => [
                    ['type' => 'referrals', 'weight' => 1.0],
                    ['type' => 'team_volume', 'weight' => 0.001]
                ],
                'rewards' => [
                    ['type' => 'commission_boost', 'multiplier' => 1.25, 'description' => '25% commission increase']
                ],
                'is_active' => true,
                'is_recurring' => true,
                'recurrence_pattern' => ['frequency' => 'monthly', 'week' => 2],
                'status' => 'active',
                'participation_requirements' => [
                    ['type' => 'active_subscription', 'value' => true]
                ]
            ]
        ];

        foreach ($programs as $programData) {
            IncentiveProgram::create($programData);
        }

        $this->command->info('Created ' . count($programs) . ' incentive programs');
    }

    private function createRecognitionEvents(): void
    {
        $events = [
            [
                'name' => 'Annual MyGrowNet Gala 2025',
                'slug' => 'annual-gala-2025',
                'description' => 'Annual recognition gala celebrating top performers and community achievements',
                'event_type' => 'annual_gala',
                'event_date' => now()->addMonths(3)->setTime(18, 0),
                'location' => 'Lusaka Convention Center',
                'is_virtual' => false,
                'max_attendees' => 500,
                'registration_deadline' => now()->addMonths(2),
                'eligibility_criteria' => [
                    ['type' => 'tier_level', 'operator' => '>=', 'value' => 2], // Silver and above
                    ['type' => 'consecutive_months', 'operator' => '>=', 'value' => 6]
                ],
                'awards' => [
                    ['type' => 'top_performer', 'title' => 'Elite Achiever of the Year', 'value' => 50000],
                    ['type' => 'leadership', 'title' => 'Outstanding Leadership Award', 'value' => 25000],
                    ['type' => 'community', 'title' => 'Community Champion Award', 'value' => 15000],
                    ['type' => 'newcomer', 'title' => 'Rising Star Award', 'value' => 10000]
                ],
                'status' => 'planning',
                'budget' => 200000,
                'celebration_theme' => 'Growth & Excellence',
                'dress_code' => 'Formal/Business Attire',
                'special_guests' => ['Industry Leaders', 'Financial Experts', 'Government Officials']
            ],
            [
                'name' => 'Q1 2025 Recognition Ceremony',
                'slug' => 'q1-2025-recognition',
                'description' => 'Quarterly virtual ceremony recognizing achievements and milestones',
                'event_type' => 'quarterly_ceremony',
                'event_date' => now()->addMonth()->endOfMonth()->setTime(19, 0),
                'location' => 'Virtual Event Platform',
                'is_virtual' => true,
                'max_attendees' => 1000,
                'registration_deadline' => now()->addMonth()->endOfMonth()->subDays(3),
                'eligibility_criteria' => [
                    ['type' => 'achievement_count', 'operator' => '>=', 'value' => 1]
                ],
                'awards' => [
                    ['type' => 'achievement', 'title' => 'Achievement Master', 'value' => 5000],
                    ['type' => 'referral', 'title' => 'Referral Champion', 'value' => 7500],
                    ['type' => 'education', 'title' => 'Learning Excellence', 'value' => 3000]
                ],
                'status' => 'registration_open',
                'budget' => 50000,
                'celebration_theme' => 'New Year, New Growth'
            ],
            [
                'name' => 'Elite Members Exclusive Retreat',
                'slug' => 'elite-retreat-2025',
                'description' => 'Exclusive retreat for Elite tier members with networking and recognition',
                'event_type' => 'exclusive_retreat',
                'event_date' => now()->addMonths(4)->setTime(9, 0),
                'location' => 'Victoria Falls Resort',
                'is_virtual' => false,
                'max_attendees' => 50,
                'registration_deadline' => now()->addMonths(3),
                'eligibility_criteria' => [
                    ['type' => 'tier_level', 'operator' => '=', 'value' => 5], // Elite only
                    ['type' => 'consecutive_months', 'operator' => '>=', 'value' => 12]
                ],
                'awards' => [
                    ['type' => 'elite_recognition', 'title' => 'Elite Excellence Certificate', 'value' => 0, 'certificate' => true]
                ],
                'status' => 'planning',
                'budget' => 150000,
                'celebration_theme' => 'Elite Excellence',
                'special_guests' => ['Industry Leaders', 'Financial Experts', 'Motivational Speakers']
            ],
            [
                'name' => 'Monthly Virtual Celebration',
                'slug' => 'monthly-celebration-' . now()->format('Y-m'),
                'description' => 'Monthly virtual celebration for all active members',
                'event_type' => 'virtual_celebration',
                'event_date' => now()->endOfMonth()->setTime(20, 0),
                'location' => 'Virtual Platform',
                'is_virtual' => true,
                'max_attendees' => 2000,
                'registration_deadline' => now()->endOfMonth()->subDays(1),
                'eligibility_criteria' => [
                    ['type' => 'tier_level', 'operator' => '>=', 'value' => 1] // All tiers
                ],
                'awards' => [
                    ['type' => 'participation', 'title' => 'Active Member Certificate', 'value' => 0, 'certificate' => true]
                ],
                'status' => 'registration_open',
                'budget' => 10000,
                'celebration_theme' => 'Community Unity'
            ]
        ];

        foreach ($events as $eventData) {
            RecognitionEvent::create($eventData);
        }

        $this->command->info('Created ' . count($events) . ' recognition events');
    }

    private function createMyGrowNetAchievements(): void
    {
        $achievements = Achievement::createMyGrowNetAchievements();
        
        foreach ($achievements as $achievementData) {
            Achievement::updateOrCreate(
                ['slug' => $achievementData['slug']],
                $achievementData
            );
        }

        $this->command->info('Created/updated ' . count($achievements) . ' achievements');
    }

    private function createDefaultLeaderboards(): void
    {
        $leaderboards = Leaderboard::createDefaultLeaderboards();
        $this->command->info('Created ' . count($leaderboards) . ' leaderboards');
    }
}