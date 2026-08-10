<?php

namespace App\Domain\GrowNet\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ResourceEntitlementService
{
    /**
     * Get all downloadable library resources entitled to the user based on Starter Kit & Education Level.
     */
    public function getEntitledResources(User $user): array
    {
        $userLevel = (int) ($user->current_professional_level_id ?? 1);

        // Check if user has an active starter kit purchase
        $hasStarterKit = DB::table('starter_kit_purchases')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->exists();

        $resources = DB::table('library_resources')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($res) use ($userLevel, $hasStarterKit) {
                // Dual entitlement check
                $isUnlocked = $hasStarterKit || ($res->difficulty === 'beginner') || ($userLevel >= 3);

                return [
                    'id' => $res->id,
                    'title' => $res->title,
                    'description' => $res->description,
                    'type' => $res->type, // pdf, video, audio, article, tool, pamphlet
                    'category' => $res->category,
                    'resource_url' => $isUnlocked ? $res->resource_url : null,
                    'thumbnail' => $res->thumbnail,
                    'author' => $res->author,
                    'duration_minutes' => $res->duration_minutes,
                    'difficulty' => $res->difficulty,
                    'is_unlocked' => $isUnlocked,
                    'unlock_requirement' => $isUnlocked ? 'Unlocked' : 'Requires Level 3 or Active Starter Kit',
                ];
            })
            ->toArray();

        return $resources;
    }
}
