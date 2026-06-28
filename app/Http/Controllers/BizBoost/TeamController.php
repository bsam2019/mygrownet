<?php

namespace App\Http\Controllers\BizBoost;

use App\Domain\Module\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TeamController extends Controller
{
    private const MODULE_ID = 'bizboost';

    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request)
    {
        $business = $this->getBusiness($request);
        
        $members = DB::table('bizboost_team_members')
            ->where('business_id', $business->id)
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        $pendingInvitations = DB::table('bizboost_team_invitations as i')
            ->join('bizboost_team_members as m', 'i.team_member_id', '=', 'm.id')
            ->where('m.business_id', $business->id)
            ->whereNull('i.accepted_at')
            ->where('i.expires_at', '>', now())
            ->select('i.*', 'm.name', 'm.email', 'm.role')
            ->get();

        return Inertia::render('BizBoost/Team/Index', [
            'members' => $members,
            'pendingInvitations' => $pendingInvitations,
            'teamLimit' => $this->getTeamLimit($request),
        ]);
    }

    public function invite(Request $request)
    {
        return Inertia::render('BizBoost/Team/Invite');
    }

    public function sendInvite(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|in:admin,editor,member',
            'location_id' => 'nullable|exists:bizboost_locations,id',
        ]);

        $business = $this->getBusiness($request);

        // Check team limit
        $currentCount = DB::table('bizboost_team_members')
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->count();

        $limit = $this->getTeamLimit($request);
        if ($currentCount >= $limit) {
            return back()->withErrors(['limit' => 'Team member limit reached. Please upgrade your plan.']);
        }

        // Check if already invited
        $existing = DB::table('bizboost_team_members')
            ->where('business_id', $business->id)
            ->where('email', $validated['email'])
            ->first();

        if ($existing) {
            return back()->withErrors(['email' => 'This email has already been invited.']);
        }

        DB::transaction(function () use ($validated, $business) {
            $memberId = DB::table('bizboost_team_members')->insertGetId([
                'business_id' => $business->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'location_id' => $validated['location_id'],
                'status' => 'pending',
                'invited_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $token = Str::random(64);
            $code = strtoupper(Str::random(8));

            DB::table('bizboost_team_invitations')->insert([
                'business_id' => $business->id,
                'team_member_id' => $memberId,
                'token' => $token,
                'code' => $code,
                'expires_at' => now()->addDays(7),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // TODO: Send invitation email notification
        });

        return redirect()->route('bizboost.team.index')
            ->with('success', 'Invitation sent successfully.');
    }

    public function updateRole(Request $request, $id)
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,editor,member',
        ]);

        $business = $this->getBusiness($request);

        DB::table('bizboost_team_members')
            ->where('id', $id)
            ->where('business_id', $business->id)
            ->update([
                'role' => $validated['role'],
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Role updated successfully.');
    }

    public function remove(Request $request, $id)
    {
        $business = $this->getBusiness($request);

        DB::table('bizboost_team_members')
            ->where('id', $id)
            ->where('business_id', $business->id)
            ->where('role', '!=', 'owner')
            ->delete();

        return back()->with('success', 'Team member removed.');
    }

    public function cancelInvitation(Request $request, $id)
    {
        $business = $this->getBusiness($request);

        $invitation = DB::table('bizboost_team_invitations')
            ->where('id', $id)
            ->where('business_id', $business->id)
            ->first();

        if ($invitation) {
            DB::table('bizboost_team_members')->where('id', $invitation->team_member_id)->delete();
            DB::table('bizboost_team_invitations')->where('id', $id)->delete();
        }

        return back()->with('success', 'Invitation cancelled.');
    }

    public function acceptInvitation(Request $request, $token)
    {
        $invitation = DB::table('bizboost_team_invitations')
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->whereNull('accepted_at')
            ->first();

        if (!$invitation) {
            return redirect()->route('bizboost.dashboard')
                ->withErrors(['invitation' => 'Invalid or expired invitation.']);
        }

        DB::transaction(function () use ($invitation, $request) {
            DB::table('bizboost_team_members')
                ->where('id', $invitation->team_member_id)
                ->update([
                    'user_id' => $request->user()->id,
                    'status' => 'active',
                    'joined_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('bizboost_team_invitations')
                ->where('id', $invitation->id)
                ->update(['accepted_at' => now(), 'updated_at' => now()]);
        });

        return redirect()->route('bizboost.dashboard')
            ->with('success', 'You have joined the team!');
    }

    private function getBusiness(Request $request): BizBoostBusinessModel
    {
        return BizBoostBusinessModel::where('user_id', $request->user()->id)->firstOrFail();
    }

    private function getTeamLimit(Request $request): int
    {
        $user = $request->user();
        $limits = $this->subscriptionService->getUserLimits($user, self::MODULE_ID);
        return $limits['team_members'] ?? 1;
    }
}
