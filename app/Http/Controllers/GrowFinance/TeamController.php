<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\TeamService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceTeamMemberModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function __construct(
        private TeamService $teamService
    ) {}

    /**
     * Display team members
     */
    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;
        $canAdd = $this->teamService->canAddTeamMember($request->user());

        // If team feature is not available at all (free tier), show upgrade page
        if (!$canAdd['allowed'] && !isset($canAdd['used'])) {
            return Inertia::render('GrowFinance/FeatureUpgradeRequired', [
                'feature' => 'Team Management',
                'requiredTier' => 'business',
            ]);
        }

        $members = $this->teamService->getTeamMembers($businessId);

        return Inertia::render('GrowFinance/Team/Index', [
            'members' => $members,
            'canAddMember' => $canAdd,
            'roles' => [
                GrowFinanceTeamMemberModel::ROLE_ADMIN => 'Admin',
                GrowFinanceTeamMemberModel::ROLE_ACCOUNTANT => 'Accountant',
                GrowFinanceTeamMemberModel::ROLE_VIEWER => 'Viewer',
            ],
        ]);
    }

    /**
     * Invite a new team member
     */
    public function invite(Request $request): RedirectResponse
    {
        $businessId = $request->user()->id;

        // Check if can add more members
        $canAdd = $this->teamService->canAddTeamMember($request->user());
        if (!$canAdd['allowed']) {
            return back()->with('error', $canAdd['reason']);
        }

        $validated = $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:admin,accountant,viewer',
        ]);

        $result = $this->teamService->inviteTeamMember(
            $businessId,
            $validated['email'],
            $validated['role']
        );

        if (!$result['success']) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', 'Invitation sent successfully!');
    }

    /**
     * Accept team invitation
     */
    public function acceptInvitation(Request $request, string $token): RedirectResponse
    {
        $result = $this->teamService->acceptInvitation($token);

        if (!$result['success']) {
            return redirect()->route('growfinance.dashboard')
                ->with('error', $result['error']);
        }

        return redirect()->route('growfinance.dashboard')
            ->with('success', 'You have joined the team!');
    }

    /**
     * Update team member role
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;

        $validated = $request->validate([
            'role' => 'required|in:admin,accountant,viewer',
        ]);

        $result = $this->teamService->updateTeamMember(
            $businessId,
            $id,
            $validated['role']
        );

        if (!$result['success']) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', 'Team member updated!');
    }

    /**
     * Remove team member
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;

        $result = $this->teamService->removeTeamMember($businessId, $id);

        if (!$result['success']) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', 'Team member removed.');
    }

    /**
     * Suspend team member
     */
    public function suspend(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;

        $result = $this->teamService->suspendTeamMember($businessId, $id);

        if (!$result['success']) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', 'Team member suspended.');
    }

    /**
     * Reactivate team member
     */
    public function reactivate(Request $request, int $id): RedirectResponse
    {
        $businessId = $request->user()->id;

        $result = $this->teamService->reactivateTeamMember($businessId, $id);

        if (!$result['success']) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', 'Team member reactivated.');
    }
}
