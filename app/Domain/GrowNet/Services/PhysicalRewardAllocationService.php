<?php

namespace App\Domain\GrowNet\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class PhysicalRewardAllocationService
{
    /**
     * Automatically allocate physical position reward when user achieves a new Education Level.
     */
    public function allocateForLevel(User $user, int $level): ?object
    {
        // Find physical reward configured for this level category
        $rewardMap = [
            1 => 'MyGrowNet Starter Kit',
            2 => 'Smartphone or Tablet Package',
            3 => 'Smartphone or Tablet Package',
            4 => 'Motorbike Package',
            5 => 'Car Package',
            6 => 'Luxury Car Package',
            7 => 'Property Investment Package',
        ];

        $rewardName = $rewardMap[$level] ?? null;
        if (!$rewardName) return null;

        $reward = DB::table('physical_rewards')->where('name', 'like', "%{$rewardName}%")->first();
        if (!$reward) return null;

        // Check if allocation already exists
        $existing = DB::table('physical_reward_allocations')
            ->where('user_id', $user->id)
            ->where('physical_reward_id', $reward->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $allocationId = DB::table('physical_reward_allocations')->insertGetId([
            'user_id' => $user->id,
            'physical_reward_id' => $reward->id,
            'allocated_at' => now(),
            'status' => 'allocated', // allocated -> claimed -> approved -> delivered
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('physical_reward_allocations')->find($allocationId);
    }
}
