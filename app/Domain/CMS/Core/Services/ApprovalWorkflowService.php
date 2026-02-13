<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\ApprovalRequestModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ApprovalStepModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ApprovalChainModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel;
use App\Notifications\CMS\ApprovalRequestedNotification;
use App\Notifications\CMS\ApprovalActionNotification;
use Illuminate\Support\Facades\DB;

class ApprovalWorkflowService
{
    public function __construct(
        private AuditTrailService $auditTrail,
        private CompanySettingsService $settingsService
    ) {}

    /**
     * Create an approval request for an entity
     */
    public function createApprovalRequest(
        int $companyId,
        string $entityType,
        int $entityId,
        float $amount,
        int $requestedBy,
        ?string $notes = null
    ): ApprovalRequestModel {
        return DB::transaction(function () use ($companyId, $entityType, $entityId, $amount, $requestedBy, $notes) {
            // Find matching approval chain
            $chain = $this->findApprovalChain($companyId, $entityType, $amount);

            if (!$chain) {
                throw new \DomainException('No approval chain found for this request');
            }

            // Create approval request
            $request = ApprovalRequestModel::create([
                'company_id' => $companyId,
                'approvable_type' => $this->getApprovableType($entityType),
                'approvable_id' => $entityId,
                'request_type' => $entityType,
                'amount' => $amount,
                'status' => 'pending',
                'requested_by' => $requestedBy,
                'request_notes' => $notes,
                'approval_level' => 1,
                'required_levels' => count($chain->approval_steps),
                'submitted_at' => now(),
            ]);

            // Create approval steps
            foreach ($chain->approval_steps as $step) {
                ApprovalStepModel::create([
                    'approval_request_id' => $request->id,
                    'step_level' => $step['level'],
                    'approver_role' => $step['role'],
                    'status' => 'pending',
                ]);
            }

            // Assign first step to appropriate approvers
            $this->assignStepApprovers($request, 1);

            // Send notifications to first level approvers
            $this->notifyApprovers($request);

            // Log audit trail
            $this->auditTrail->log(
                companyId: $companyId,
                userId: $requestedBy,
                entityType: 'approval_request',
                entityId: $request->id,
                action: 'created',
                newValues: $request->toArray()
            );

            return $request;
        });
    }

    /**
     * Approve a step in the approval workflow
     */
    public function approveStep(
        int $requestId,
        int $approverId,
        ?string $comments = null
    ): ApprovalRequestModel {
        return DB::transaction(function () use ($requestId, $approverId, $comments) {
            $request = ApprovalRequestModel::with('steps')->findOrFail($requestId);

            if (!$request->isPending()) {
                throw new \DomainException('This approval request is not pending');
            }

            // Get current step
            $currentStep = $request->currentStep();

            if (!$currentStep) {
                throw new \DomainException('No pending step found');
            }

            // Verify approver has permission
            if ($currentStep->approver_id && $currentStep->approver_id !== $approverId) {
                throw new \DomainException('You are not assigned to approve this step');
            }

            // Update step
            $currentStep->update([
                'approver_id' => $approverId,
                'status' => 'approved',
                'comments' => $comments,
                'actioned_at' => now(),
            ]);

            // Check if all steps are complete
            if ($request->approval_level >= $request->required_levels) {
                // All levels approved - complete the request
                $request->update([
                    'status' => 'approved',
                    'completed_at' => now(),
                ]);

                // Notify requester of approval
                $this->notifyRequester($request, 'approved');

                // Execute approval action (update entity status)
                $this->executeApprovalAction($request);
            } else {
                // Move to next level
                $request->update([
                    'approval_level' => $request->approval_level + 1,
                ]);

                // Assign next level approvers
                $this->assignStepApprovers($request, $request->approval_level);

                // Notify next level approvers
                $this->notifyApprovers($request);
            }

            // Log audit trail
            $this->auditTrail->log(
                companyId: $request->company_id,
                userId: $approverId,
                entityType: 'approval_request',
                entityId: $request->id,
                action: 'step_approved',
                newValues: [
                    'step_level' => $currentStep->step_level,
                    'comments' => $comments,
                ]
            );

            return $request->fresh();
        });
    }

    /**
     * Reject an approval request
     */
    public function rejectStep(
        int $requestId,
        int $approverId,
        string $reason
    ): ApprovalRequestModel {
        return DB::transaction(function () use ($requestId, $approverId, $reason) {
            $request = ApprovalRequestModel::with('steps')->findOrFail($requestId);

            if (!$request->isPending()) {
                throw new \DomainException('This approval request is not pending');
            }

            // Get current step
            $currentStep = $request->currentStep();

            if (!$currentStep) {
                throw new \DomainException('No pending step found');
            }

            // Verify approver has permission
            if ($currentStep->approver_id && $currentStep->approver_id !== $approverId) {
                throw new \DomainException('You are not assigned to approve this step');
            }

            // Update step
            $currentStep->update([
                'approver_id' => $approverId,
                'status' => 'rejected',
                'comments' => $reason,
                'actioned_at' => now(),
            ]);

            // Reject the entire request
            $request->update([
                'status' => 'rejected',
                'completed_at' => now(),
            ]);

            // Notify requester of rejection
            $this->notifyRequester($request, 'rejected', $reason);

            // Execute rejection action (update entity status)
            $this->executeRejectionAction($request, $reason);

            // Log audit trail
            $this->auditTrail->log(
                companyId: $request->company_id,
                userId: $approverId,
                entityType: 'approval_request',
                entityId: $request->id,
                action: 'rejected',
                newValues: [
                    'step_level' => $currentStep->step_level,
                    'reason' => $reason,
                ]
            );

            return $request->fresh();
        });
    }

    /**
     * Cancel an approval request
     */
    public function cancelRequest(int $requestId, int $userId, string $reason): ApprovalRequestModel
    {
        $request = ApprovalRequestModel::findOrFail($requestId);

        if (!$request->isPending()) {
            throw new \DomainException('Only pending requests can be cancelled');
        }

        // Only requester can cancel
        if ($request->requested_by !== $userId) {
            throw new \DomainException('Only the requester can cancel this request');
        }

        $request->update([
            'status' => 'cancelled',
            'completed_at' => now(),
        ]);

        // Log audit trail
        $this->auditTrail->log(
            companyId: $request->company_id,
            userId: $userId,
            entityType: 'approval_request',
            entityId: $request->id,
            action: 'cancelled',
            newValues: ['reason' => $reason]
        );

        return $request->fresh();
    }

    /**
     * Get pending approvals for a user
     */
    public function getPendingApprovalsForUser(int $userId): array
    {
        $user = CmsUserModel::with('role')->findOrFail($userId);
        $companyId = $user->company_id;
        $userRole = $user->role->name;

        return ApprovalRequestModel::forCompany($companyId)
            ->pending()
            ->with(['requestedBy.user', 'approvable', 'steps'])
            ->whereHas('steps', function ($query) use ($userId, $userRole) {
                $query->where('status', 'pending')
                    ->where(function ($q) use ($userId, $userRole) {
                        $q->where('approver_id', $userId)
                          ->orWhere('approver_role', $userRole);
                    });
            })
            ->orderBy('submitted_at', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Get approval history for an entity
     */
    public function getApprovalHistory(string $entityType, int $entityId): array
    {
        return ApprovalRequestModel::where('approvable_type', $this->getApprovableType($entityType))
            ->where('approvable_id', $entityId)
            ->with(['requestedBy.user', 'steps.approver.user'])
            ->orderBy('submitted_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Check if entity requires approval
     */
    public function requiresApproval(int $companyId, string $entityType, float $amount): bool
    {
        // Check settings first
        $requiresApproval = $this->settingsService->requiresApproval($companyId, $entityType, $amount);

        if (!$requiresApproval) {
            return false;
        }

        // Check if approval chain exists
        $chain = $this->findApprovalChain($companyId, $entityType, $amount);

        return $chain !== null;
    }

    /**
     * Find matching approval chain
     */
    private function findApprovalChain(int $companyId, string $entityType, float $amount): ?ApprovalChainModel
    {
        return ApprovalChainModel::forCompany($companyId)
            ->active()
            ->forEntityType($entityType)
            ->orderBy('priority', 'desc')
            ->get()
            ->first(fn($chain) => $chain->matchesAmount($amount));
    }

    /**
     * Assign approvers to a step based on role
     */
    private function assignStepApprovers(ApprovalRequestModel $request, int $stepLevel): void
    {
        $step = $request->steps()->where('step_level', $stepLevel)->first();

        if (!$step) {
            return;
        }

        // Find users with the required role
        $approvers = CmsUserModel::where('company_id', $request->company_id)
            ->whereHas('role', function ($query) use ($step) {
                $query->where('name', $step->approver_role);
            })
            ->get();

        // If only one approver, assign directly
        if ($approvers->count() === 1) {
            $step->update(['approver_id' => $approvers->first()->id]);
        }
    }

    /**
     * Notify approvers of pending approval
     */
    private function notifyApprovers(ApprovalRequestModel $request): void
    {
        $currentStep = $request->currentStep();

        if (!$currentStep) {
            return;
        }

        // Get approvers for this step
        $approvers = CmsUserModel::where('company_id', $request->company_id)
            ->whereHas('role', function ($query) use ($currentStep) {
                $query->where('name', $currentStep->approver_role);
            })
            ->with('user')
            ->get();

        foreach ($approvers as $approver) {
            if ($approver->user) {
                $approver->user->notify(new ApprovalRequestedNotification([
                    'request_id' => $request->id,
                    'request_type' => $request->request_type,
                    'amount' => $request->amount,
                    'requested_by' => $request->requestedBy->user->name,
                    'step_level' => $currentStep->step_level,
                ]));
            }
        }
    }

    /**
     * Notify requester of approval action
     */
    private function notifyRequester(ApprovalRequestModel $request, string $action, ?string $reason = null): void
    {
        $requester = $request->requestedBy;

        if ($requester && $requester->user) {
            $requester->user->notify(new ApprovalActionNotification([
                'request_id' => $request->id,
                'request_type' => $request->request_type,
                'amount' => $request->amount,
                'action' => $action,
                'reason' => $reason,
            ]));
        }
    }

    /**
     * Execute approval action on the entity
     */
    private function executeApprovalAction(ApprovalRequestModel $request): void
    {
        $entity = $request->approvable;

        if (!$entity) {
            return;
        }

        // Update entity status based on type
        if (method_exists($entity, 'markAsApproved')) {
            $entity->markAsApproved();
        } elseif ($entity->hasAttribute('approval_status')) {
            $entity->update(['approval_status' => 'approved']);
        }
    }

    /**
     * Execute rejection action on the entity
     */
    private function executeRejectionAction(ApprovalRequestModel $request, string $reason): void
    {
        $entity = $request->approvable;

        if (!$entity) {
            return;
        }

        // Update entity status based on type
        if (method_exists($entity, 'markAsRejected')) {
            $entity->markAsRejected($reason);
        } elseif ($entity->hasAttribute('approval_status')) {
            $entity->update([
                'approval_status' => 'rejected',
                'rejection_reason' => $reason,
            ]);
        }
    }

    /**
     * Get approvable type from entity type
     */
    private function getApprovableType(string $entityType): string
    {
        return match ($entityType) {
            'expense' => 'App\Infrastructure\Persistence\Eloquent\CMS\ExpenseModel',
            'quotation' => 'App\Infrastructure\Persistence\Eloquent\CMS\QuotationModel',
            'payment' => 'App\Infrastructure\Persistence\Eloquent\CMS\PaymentModel',
            default => throw new \InvalidArgumentException("Unknown entity type: {$entityType}"),
        };
    }
}
