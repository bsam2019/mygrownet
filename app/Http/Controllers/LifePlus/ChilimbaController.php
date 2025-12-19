<?php

namespace App\Http\Controllers\LifePlus;

use App\Http\Controllers\Controller;
use App\Domain\LifePlus\Services\ChilimbaService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChilimbaController extends Controller
{
    public function __construct(protected ChilimbaService $chilimbaService) {}

    // ==================== GROUPS ====================

    public function index()
    {
        return Inertia::render('LifePlus/Money/Chilimba/Index', [
            'groups' => $this->chilimbaService->getGroups(auth()->id()),
        ]);
    }

    public function show(int $id)
    {
        $group = $this->chilimbaService->getGroup($id, auth()->id());
        if (!$group) {
            return redirect()->route('lifeplus.chilimba.index')->with('error', 'Group not found');
        }

        return Inertia::render('LifePlus/Money/Chilimba/Show', [
            'group' => $group,
            'contributions' => $this->chilimbaService->getContributions($id, auth()->id()),
            'summary' => $this->chilimbaService->getContributionSummary($id, auth()->id()),
            'payoutQueue' => $this->chilimbaService->getPayoutQueue($id, auth()->id()),
            'loans' => $this->chilimbaService->getLoans($id, auth()->id()),
            'contributionTypes' => $this->chilimbaService->getContributionTypes($id, auth()->id()),
            'specialContributions' => $this->chilimbaService->getSpecialContributions($id, auth()->id()),
            'specialSummary' => $this->chilimbaService->getSpecialContributionSummary($id, auth()->id()),
            'auditLog' => $this->chilimbaService->getAuditLog($id, auth()->id(), 20),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'meeting_frequency' => 'required|in:weekly,bi-weekly,monthly',
            'meeting_day' => 'nullable|string|max:50',
            'meeting_time' => 'nullable|date_format:H:i',
            'meeting_location' => 'nullable|string|max:500',
            'min_contribution' => 'required|numeric|min:1|max:100000',
            'max_contribution' => 'nullable|numeric|min:1|max:100000',
            'initial_contribution' => 'nullable|numeric|min:0|max:500000',
            'teacher_contribution' => 'nullable|numeric|min:0|max:10000',
            'absence_penalty' => 'nullable|numeric|min:0|max:10000',
            'total_members' => 'required|integer|min:2|max:100',
            'start_date' => 'nullable|date',
            'user_role' => 'required|in:member,secretary,treasurer',
        ]);

        $group = $this->chilimbaService->createGroup(auth()->id(), $validated);
        return redirect()->route('lifeplus.chilimba.show', $group['id'])->with('success', 'Group created');
    }


    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'meeting_day' => 'nullable|string|max:50',
            'meeting_time' => 'nullable|date_format:H:i',
            'meeting_location' => 'nullable|string|max:500',
            'contribution_amount' => 'nullable|numeric|min:1|max:100000',
        ]);

        $group = $this->chilimbaService->updateGroup($id, auth()->id(), $validated);
        if (!$group) {
            return back()->with('error', 'Group not found');
        }
        return back()->with('success', 'Group updated');
    }

    public function destroy(int $id)
    {
        $deleted = $this->chilimbaService->deleteGroup($id, auth()->id());
        if (!$deleted) {
            return back()->with('error', 'Group not found');
        }
        return redirect()->route('lifeplus.chilimba.index')->with('success', 'Group deleted');
    }

    // ==================== MEMBERS ====================

    public function storeMembers(Request $request, int $groupId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'position_in_queue' => 'nullable|integer|min:1',
        ]);

        $member = $this->chilimbaService->addMember($groupId, auth()->id(), $validated);
        if (!$member) {
            return back()->with('error', 'Failed to add member');
        }
        return back()->with('success', 'Member added');
    }

    public function updateMember(Request $request, int $memberId)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'position_in_queue' => 'nullable|integer|min:1',
        ]);

        $member = $this->chilimbaService->updateMember($memberId, auth()->id(), $validated);
        if (!$member) {
            return back()->with('error', 'Member not found');
        }
        return back()->with('success', 'Member updated');
    }

    public function destroyMember(Request $request, int $memberId)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        
        $deleted = $this->chilimbaService->removeMember($memberId, auth()->id(), $request->reason);
        if (!$deleted) {
            return back()->with('error', 'Member not found');
        }
        return back()->with('success', 'Member removed');
    }

    // ==================== CONTRIBUTIONS ====================

    public function storeContribution(Request $request, int $groupId)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:lifeplus_chilimba_members,id',
            'contribution_date' => 'required|date|before_or_equal:today',
            'amount' => 'required|numeric|min:1|max:100000',
            'payment_method' => 'required|in:cash,mobile_money',
            'receipt_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->chilimbaService->recordContribution($groupId, auth()->id(), $validated);
            return back()->with('success', 'Contribution recorded');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function storeBulkContributions(Request $request, int $groupId)
    {
        $validated = $request->validate([
            'contributions' => 'required|array|min:1',
            'contributions.*.member_id' => 'required|exists:lifeplus_chilimba_members,id',
            'contributions.*.contribution_date' => 'required|date|before_or_equal:today',
            'contributions.*.amount' => 'required|numeric|min:1',
            'contributions.*.payment_method' => 'required|in:cash,mobile_money',
        ]);

        $results = $this->chilimbaService->recordBulkContributions($groupId, auth()->id(), $validated['contributions']);
        $errors = collect($results)->filter(fn($r) => isset($r['error']))->count();
        
        if ($errors > 0) {
            return back()->with('warning', "Recorded with {$errors} errors");
        }
        return back()->with('success', 'All contributions recorded');
    }


    public function updateContribution(Request $request, int $id)
    {
        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:1|max:100000',
            'payment_method' => 'nullable|in:cash,mobile_money',
            'notes' => 'nullable|string|max:500',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $reason = $validated['reason'];
            unset($validated['reason']);
            $this->chilimbaService->updateContribution($id, auth()->id(), $validated, $reason);
            return back()->with('success', 'Contribution updated');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroyContribution(Request $request, int $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        
        $deleted = $this->chilimbaService->deleteContribution($id, auth()->id(), $request->reason);
        if (!$deleted) {
            return back()->with('error', 'Cannot delete contribution');
        }
        return back()->with('success', 'Contribution deleted');
    }

    public function contributionSummary(Request $request, int $groupId)
    {
        $month = $request->query('month');
        $summary = $this->chilimbaService->getContributionSummary($groupId, auth()->id(), $month);
        
        if ($request->wantsJson()) {
            return response()->json($summary);
        }
        return back()->with('summary', $summary);
    }

    // ==================== PAYOUTS ====================

    public function storePayout(Request $request, int $groupId)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:lifeplus_chilimba_members,id',
            'payout_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'cycle_number' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $payout = $this->chilimbaService->recordPayout($groupId, auth()->id(), $validated);
        if (!$payout) {
            return back()->with('error', 'Failed to record payout');
        }
        return back()->with('success', 'Payout recorded');
    }

    // ==================== LOANS ====================

    public function storeLoan(Request $request, int $groupId)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:lifeplus_chilimba_members,id',
            'loan_amount' => 'required|numeric|min:1|max:100000',
            'interest_rate' => 'required|numeric|min:0|max:50',
            'due_date' => 'required|date|after:today',
            'purpose' => 'nullable|string|max:500',
        ]);

        $loan = $this->chilimbaService->requestLoan($groupId, auth()->id(), $validated);
        if (!$loan) {
            return back()->with('error', 'Failed to request loan');
        }
        return back()->with('success', 'Loan request submitted');
    }

    public function approveLoan(int $loanId)
    {
        $loan = $this->chilimbaService->approveLoan($loanId, auth()->id());
        if (!$loan) {
            return back()->with('error', 'Cannot approve loan');
        }
        return back()->with('success', 'Loan approved');
    }

    public function storeLoanPayment(Request $request, int $loanId)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'nullable|date|before_or_equal:today',
            'payment_method' => 'required|in:cash,mobile_money',
            'notes' => 'nullable|string|max:500',
        ]);

        $loan = $this->chilimbaService->recordLoanPayment($loanId, auth()->id(), $validated);
        if (!$loan) {
            return back()->with('error', 'Failed to record payment');
        }
        return back()->with('success', 'Payment recorded');
    }

    // ==================== SPECIAL CONTRIBUTIONS ====================

    public function getContributionTypes(int $groupId)
    {
        return response()->json([
            'types' => $this->chilimbaService->getContributionTypes($groupId, auth()->id()),
        ]);
    }

    public function storeContributionType(Request $request, int $groupId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:10',
            'default_amount' => 'nullable|numeric|min:0|max:100000',
            'is_mandatory' => 'nullable|boolean',
        ]);

        $type = $this->chilimbaService->createContributionType($groupId, auth()->id(), $validated);
        if (!$type) {
            return back()->with('error', 'Failed to create contribution type');
        }
        return back()->with('success', 'Contribution type created');
    }

    public function storeSpecialContribution(Request $request, int $groupId)
    {
        $validated = $request->validate([
            'type_id' => 'required|exists:lifeplus_chilimba_contribution_types,id',
            'member_id' => 'required|exists:lifeplus_chilimba_members,id',
            'beneficiary_id' => 'nullable|exists:lifeplus_chilimba_members,id',
            'beneficiary_name' => 'nullable|string|max:255',
            'contribution_date' => 'required|date|before_or_equal:today',
            'amount' => 'required|numeric|min:1|max:100000',
            'payment_method' => 'required|in:cash,mobile_money',
            'notes' => 'nullable|string|max:500',
        ]);

        $contribution = $this->chilimbaService->recordSpecialContribution($groupId, auth()->id(), $validated);
        if (!$contribution) {
            return back()->with('error', 'Failed to record contribution');
        }
        return back()->with('success', 'Special contribution recorded');
    }

    // ==================== MEETINGS ====================

    public function storeMeeting(Request $request, int $groupId)
    {
        $validated = $request->validate([
            'meeting_date' => 'required|date',
            'attendees' => 'nullable|array',
            'total_collected' => 'nullable|numeric|min:0',
            'payout_given_to' => 'nullable|string|max:255',
            'decisions' => 'nullable|string|max:2000',
            'next_meeting_date' => 'nullable|date|after:meeting_date',
        ]);

        $meeting = $this->chilimbaService->recordMeeting($groupId, auth()->id(), $validated);
        if (!$meeting) {
            return back()->with('error', 'Failed to record meeting');
        }
        return back()->with('success', 'Meeting recorded');
    }
}
