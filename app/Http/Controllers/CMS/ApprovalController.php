<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\ApprovalWorkflowService;
use App\Infrastructure\Persistence\Eloquent\CMS\ApprovalRequestModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ApprovalChainModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApprovalController extends Controller
{
    public function __construct(
        private ApprovalWorkflowService $approvalService
    ) {}

    /**
     * Display pending approvals for current user
     */
    public function index(Request $request): Response
    {
        $userId = $request->user()->cmsUser->id;
        $companyId = $request->user()->cmsUser->company_id;

        $pendingApprovals = $this->approvalService->getPendingApprovalsForUser($userId);

        // Get all approval requests for the company (for history)
        $allRequests = ApprovalRequestModel::forCompany($companyId)
            ->with(['requestedBy.user', 'approvable', 'steps.approver.user'])
            ->orderBy('submitted_at', 'desc')
            ->paginate(20);

        return Inertia::render('CMS/Approvals/Index', [
            'pendingApprovals' => $pendingApprovals,
            'allRequests' => $allRequests,
        ]);
    }

    /**
     * Show approval request details
     */
    public function show(Request $request, int $id): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $approval = ApprovalRequestModel::forCompany($companyId)
            ->with(['requestedBy.user', 'approvable', 'steps.approver.user'])
            ->findOrFail($id);

        return Inertia::render('CMS/Approvals/Show', [
            'approval' => $approval,
        ]);
    }

    /**
     * Approve a step
     */
    public function approve(Request $request, int $id)
    {
        $validated = $request->validate([
            'comments' => 'nullable|string|max:1000',
        ]);

        $approverId = $request->user()->cmsUser->id;

        try {
            $this->approvalService->approveStep($id, $approverId, $validated['comments'] ?? null);

            return back()->with('success', 'Approval step completed successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to approve: ' . $e->getMessage());
        }
    }

    /**
     * Reject a request
     */
    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $approverId = $request->user()->cmsUser->id;

        try {
            $this->approvalService->rejectStep($id, $approverId, $validated['reason']);

            return back()->with('success', 'Request rejected');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reject: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a request
     */
    public function cancel(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $userId = $request->user()->cmsUser->id;

        try {
            $this->approvalService->cancelRequest($id, $userId, $validated['reason']);

            return back()->with('success', 'Request cancelled');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel: ' . $e->getMessage());
        }
    }

    /**
     * Manage approval chains
     */
    public function chains(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $chains = ApprovalChainModel::forCompany($companyId)
            ->orderBy('priority', 'desc')
            ->orderBy('entity_type')
            ->get();

        return Inertia::render('CMS/Approvals/Chains', [
            'chains' => $chains,
        ]);
    }

    /**
     * Store new approval chain
     */
    public function storeChain(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'entity_type' => 'required|in:expense,quotation,payment',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gt:min_amount',
            'approval_steps' => 'required|array|min:1',
            'approval_steps.*.level' => 'required|integer|min:1',
            'approval_steps.*.role' => 'required|in:owner,manager,accountant',
            'priority' => 'nullable|integer|min:0',
        ]);

        $companyId = $request->user()->cmsUser->company_id;

        ApprovalChainModel::create([
            ...$validated,
            'company_id' => $companyId,
            'is_active' => true,
        ]);

        return back()->with('success', 'Approval chain created successfully');
    }

    /**
     * Update approval chain
     */
    public function updateChain(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gt:min_amount',
            'approval_steps' => 'required|array|min:1',
            'approval_steps.*.level' => 'required|integer|min:1',
            'approval_steps.*.role' => 'required|in:owner,manager,accountant',
            'priority' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        $companyId = $request->user()->cmsUser->company_id;

        $chain = ApprovalChainModel::forCompany($companyId)->findOrFail($id);
        $chain->update($validated);

        return back()->with('success', 'Approval chain updated successfully');
    }

    /**
     * Delete approval chain
     */
    public function deleteChain(Request $request, int $id)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $chain = ApprovalChainModel::forCompany($companyId)->findOrFail($id);
        $chain->delete();

        return back()->with('success', 'Approval chain deleted successfully');
    }
}
