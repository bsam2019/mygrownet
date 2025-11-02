<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfessionalLevelSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        DB::table('professional_levels')->delete();

        $levels = [
            [
                'level' => 1,
                'name' => 'Associate',
                'slug' => 'associate',
                'network_size' => '3',
                'role' => 'New member, learning',
                'bp_required' => 100,
                'lp_required' => 0,
                'min_time' => 'Immediate',
                'additional_requirements' => 'Registration complete',
                'milestone_bonus' => null,
                'profit_share_multiplier' => '1.0x',
                'commission_rate' => '15%',
                'color' => 'gray',
                'benefits' => json_encode([
                    'Basic educational content',
                    'Peer circle access',
                    '7-level commission structure (15%)',
                    'Monthly qualification: 100 BP',
                    'Profit-sharing: 1.0x base share'
                ]),
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'level' => 2,
                'name' => 'Professional',
                'slug' => 'professional',
                'network_size' => '9',
                'role' => 'Skilled member, applying',
                'bp_required' => 200,
                'lp_required' => 2500,
                'min_time' => '1 month active',
                'additional_requirements' => '3 direct referrals',
                'milestone_bonus' => 'K500',
                'profit_share_multiplier' => '1.2x',
                'commission_rate' => '10%',
                'color' => 'blue',
                'benefits' => json_encode([
                    'Advanced educational content',
                    'Group mentorship access',
                    'Level 2 commissions (10%)',
                    'Monthly qualification: 200 BP',
                    'Profit-sharing: 1.2x base share',
                    'Promotion bonus: K500'
                ]),
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'level' => 3,
                'name' => 'Senior',
                'slug' => 'senior',
                'network_size' => '27',
                'role' => 'Experienced, team building',
                'bp_required' => 300,
                'lp_required' => 4000,
                'min_time' => '3 months active',
                'additional_requirements' => '2 active direct referrals, 1 course completed',
                'milestone_bonus' => 'K1,500 + Smartphone',
                'profit_share_multiplier' => '1.5x',
                'commission_rate' => '8%',
                'color' => 'green',
                'benefits' => json_encode([
                    'Premium content library',
                    '1-on-1 mentorship sessions',
                    'Level 3 commissions (8%)',
                    'Team building bonuses',
                    'Monthly qualification: 300 BP',
                    'Profit-sharing: 1.5x base share',
                    'Promotion bonus: K1,500 + Smartphone'
                ]),
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'level' => 4,
                'name' => 'Manager',
                'slug' => 'manager',
                'network_size' => '81',
                'role' => 'Team leader',
                'bp_required' => 400,
                'lp_required' => 12500,
                'min_time' => '6 months active',
                'additional_requirements' => '1 Professional in downline, 3 courses completed',
                'milestone_bonus' => 'K5,000 + Motorbike',
                'profit_share_multiplier' => '2.0x',
                'commission_rate' => '6%',
                'color' => 'purple',
                'benefits' => json_encode([
                    'Leadership training programs',
                    'Level 4 commissions (6%)',
                    'Team performance bonuses',
                    'Booster fund: K5,000',
                    'Monthly qualification: 400 BP',
                    'Profit-sharing: 2.0x base share',
                    'Promotion bonus: K5,000 + Motorbike'
                ]),
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'level' => 5,
                'name' => 'Director',
                'slug' => 'director',
                'network_size' => '243',
                'role' => 'Strategic leader',
                'bp_required' => 500,
                'lp_required' => 60000,
                'min_time' => '12 months active',
                'additional_requirements' => '1 Senior in downline, 5 courses completed',
                'milestone_bonus' => 'K15,000 + Vehicle',
                'profit_share_multiplier' => '2.5x',
                'commission_rate' => '4%',
                'color' => 'indigo',
                'benefits' => json_encode([
                    'Strategic leadership content',
                    'Level 5 commissions (4%)',
                    'Business facilitation services',
                    'Booster fund: K15,000',
                    'Monthly qualification: 500 BP',
                    'Profit-sharing: 2.5x base share',
                    'Promotion bonus: K15,000 + Vehicle'
                ]),
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'level' => 6,
                'name' => 'Executive',
                'slug' => 'executive',
                'network_size' => '729',
                'role' => 'Top performer',
                'bp_required' => 600,
                'lp_required' => 160000,
                'min_time' => '18 months active',
                'additional_requirements' => '1 Manager in downline, 10 courses completed',
                'milestone_bonus' => 'K50,000 + Luxury',
                'profit_share_multiplier' => '3.0x',
                'commission_rate' => '3%',
                'color' => 'yellow',
                'benefits' => json_encode([
                    'Executive coaching access',
                    'Level 6 commissions (3%)',
                    'Innovation lab participation',
                    'Booster fund: K50,000',
                    'Monthly qualification: 600 BP',
                    'Profit-sharing: 3.0x base share',
                    'Promotion bonus: K50,000 + Luxury'
                ]),
                'sort_order' => 6,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'level' => 7,
                'name' => 'Ambassador',
                'slug' => 'ambassador',
                'network_size' => '2,187',
                'role' => 'Brand representative',
                'bp_required' => 800,
                'lp_required' => 350000,
                'min_time' => '24 months active',
                'additional_requirements' => '1 Director in downline, 15 courses, 1 project participation',
                'milestone_bonus' => 'K150,000 + Property',
                'profit_share_multiplier' => '4.0x',
                'commission_rate' => '2%',
                'color' => 'red',
                'benefits' => json_encode([
                    'VIP brand ambassador status',
                    'Level 7 commissions (2%)',
                    'Exclusive events & retreats',
                    'Booster fund: K150,000',
                    'Monthly qualification: 800 BP',
                    'Profit-sharing: 4.0x base share (MAX)',
                    'Promotion bonus: K150,000 + Property'
                ]),
                'sort_order' => 7,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('professional_levels')->insert($levels);
        
        $this->command->info('Professional levels seeded successfully!');
    }
}
