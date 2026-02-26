<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use App\Application\StarterKit\UseCases\GiftStarterKitUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class GiftController extends Controller
{
    public function __construct(
        private GiftStarterKitUseCase $giftStarterKitUseCase
    ) {}

    /**
     * Gift a starter kit to a downline member
     */
    public function giftStarterKit(Request $request)
    {
        \Log::info('Gift starter kit request received', [
            'user_id' => Auth::id(),
            'data' => $request->all()
        ]);

        $validated = $request->validate([
            'recipient_id' => 'required|integer|exists:users,id',
            'tier' => 'required|in:basic,premium',
        ]);

        try {
            \Log::info('Executing gift use case', $validated);
            
            $result = $this->giftStarterKitUseCase->execute(
                Auth::id(),
                $validated['recipient_id'],
                $validated['tier']
            );

            \Log::info('Gift successful', $result);
            return back()->with('success', $result['message']);
        } catch (\InvalidArgumentException $e) {
            \Log::error('Gift validation error', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Gift system error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to gift starter kit. Please try again.');
        }
    }

    /**
     * Get gift limits for current user
     */
    public function getLimits()
    {
        try {
            \Log::info('Gift limits requested', ['user_id' => Auth::id()]);
            $limits = $this->giftStarterKitUseCase->getGiftLimits(Auth::id());
            \Log::info('Gift limits response', $limits);
            return response()->json($limits);
        } catch (\Exception $e) {
            \Log::error('Gift limits error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to fetch gift limits', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get gift history for current user
     */
    public function getHistory()
    {
        $gifts = Auth::user()
            ->giftsGiven()
            ->with('recipient:id,name,phone')
            ->latest()
            ->paginate(20);

        return Inertia::render('GrowNet/GiftHistory', [
            'gifts' => $gifts,
        ]);
    }

    /**
     * Get level members with starter kit status
     */
    public function getLevelMembers(Request $request, int $level)
    {
        $user = Auth::user();
        
        // Get members at specified level
        $members = \App\Models\UserNetwork::where('referrer_id', $user->id)
            ->where('level', $level)
            ->with(['user.starterKitPurchases', 'user.currentMembershipTier'])
            ->get()
            ->map(function ($network) {
                $member = $network->user;
                $hasStarterKit = $member->starterKitPurchases()->exists();
                
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'phone' => $member->phone,
                    'tier' => $member->currentMembershipTier->name ?? 'Associate',
                    'joined_date' => $member->created_at->format('M Y'),
                    'has_starter_kit' => $hasStarterKit,
                    'direct_referrals' => $member->directReferrals()->count(),
                    'team_size' => \App\Models\UserNetwork::where('referrer_id', $member->id)->count(),
                ];
            });

        return response()->json([
            'level' => $level,
            'members' => $members,
        ]);
    }
}
