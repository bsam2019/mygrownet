<?php

namespace App\Domain\GrowNet\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SkillsDemandService
{
    /**
     * Submit or express interest in learning a specific skill.
     */
    public function expressSkillInterest(User $user, string $skillTitle, ?string $notes = null): array
    {
        $slug = Str::slug($skillTitle);
        
        $skill = DB::table('skills')->where('slug', $slug)->first();
        if (!$skill) {
            $skillId = DB::table('skills')->insertGetId([
                'title' => ucwords($skillTitle),
                'slug' => $slug,
                'category' => 'Specialist Skills',
                'description' => 'Demand-driven specialist training requested by members.',
                'demand_count' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $skillId = $skill->id;
            DB::table('skills')->where('id', $skillId)->increment('demand_count');
        }

        DB::table('member_skill_interests')->updateOrInsert(
            ['user_id' => $user->id, 'skill_id' => $skillId],
            [
                'status' => 'interested',
                'custom_request_notes' => $notes,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // Check if demand threshold (30 requests) is met to auto-create training opportunity
        $demandCount = DB::table('skills')->where('id', $skillId)->value('demand_count') ?? 0;
        if ($demandCount >= 30) {
            $existingOpp = DB::table('training_opportunities')->where('skill_id', $skillId)->exists();
            if (!$existingOpp) {
                DB::table('training_opportunities')->insert([
                    'title' => ucwords($skillTitle) . ' Specialist Training',
                    'skill_id' => $skillId,
                    'status' => 'pre_registration',
                    'min_demand_threshold' => 30,
                    'current_registrations' => $demandCount,
                    'max_capacity' => 50,
                    'location' => 'Online + Workshops',
                    'fee' => 0.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return [
            'success' => true,
            'message' => 'Skill request recorded! We are aggregating demand.',
            'skill_id' => $skillId,
            'total_demand' => $demandCount,
        ];
    }
}
