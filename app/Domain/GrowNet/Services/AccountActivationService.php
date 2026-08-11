<?php

namespace App\Domain\GrowNet\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AccountActivationService
{
    /**
     * Activate or initialize a GrowNet account for a user.
     * Can be invoked from GrowStream, GrowMusic, or main domain.
     */
    public function activateGrowNet(User $user, ?string $sponsorCode = null): array
    {
        DB::beginTransaction();
        try {
            // 1. Generate referral code if missing
            if (empty($user->referral_code)) {
                $user->referral_code = 'GN-' . strtoupper(Str::random(6));
            }

            // 2. Link sponsor/referrer if provided and not yet linked
            if (!empty($sponsorCode) && empty($user->referrer_id)) {
                $sponsor = User::where('referral_code', trim($sponsorCode))
                    ->where('id', '!=', $user->id)
                    ->first();
                if ($sponsor) {
                    $user->referrer_id = $sponsor->id;
                }
            }

            // 3. Initialize points profile (PB = lifetime_points, MP = monthly_points)
            $userPoints = DB::table('user_points')->where('user_id', $user->id)->first();
            if (!$userPoints) {
                DB::table('user_points')->insert([
                    'user_id' => $user->id,
                    'lifetime_points' => 0, // PB Points
                    'monthly_points' => 0,  // MP Points
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 4. Initialize Level 1 Starter if level not set
            if (empty($user->current_professional_level)) {
                $user->current_professional_level = 'starter';
                $user->level_achieved_at = now();
            }

            // 5. Place in 3x7 Matrix if not placed
            if (Schema::hasTable('matrix_positions')) {
                $matrixExists = DB::table('matrix_positions')->where('user_id', $user->id)->exists();
                if (!$matrixExists) {
                    try {
                        app(MatrixService::class)->placeMember($user);
                    } catch (\Throwable $e) {
                        \Log::warning("Matrix placement deferred for user {$user->id}: " . $e->getMessage());
                    }
                }
            }

            // 6. Set active status flag
            $user->is_grownet_active = true;
            $user->save();

            DB::commit();

            $levelTitle = DB::table('professional_levels')
                ->where('slug', $user->current_professional_level ?? 'starter')
                ->value('name') ?? 'Level 1: Starter';

            $pbPoints = DB::table('user_points')->where('user_id', $user->id)->value('lifetime_points') ?? 0;
            $mpPoints = DB::table('user_points')->where('user_id', $user->id)->value('monthly_points') ?? 0;

            return [
                'success' => true,
                'message' => 'GrowNet Account activated successfully!',
                'grownet' => [
                    'is_active' => true,
                    'level_slug' => $user->current_professional_level ?? 'starter',
                    'level_title' => $levelTitle,
                    'referral_code' => $user->referral_code,
                    'referral_link' => url('/register?ref=' . $user->referral_code),
                    'pb_points' => (int) $pbPoints,
                    'mp_points' => (int) $mpPoints,
                    'portal_url' => config('app.url') . '/grownet',
                ],
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error("Failed to activate GrowNet account for user {$user->id}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Activation failed: ' . $e->getMessage(),
            ];
        }
    }
}
