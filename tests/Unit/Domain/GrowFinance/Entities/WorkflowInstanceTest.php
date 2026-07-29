<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\WorkflowInstance;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WorkflowInstanceTest extends TestCase
{
    private WorkflowInstance $instance;

    protected function setUp(): void
    {
        $this->instance = new WorkflowInstance(
            id: 1, businessId: 5, workflowTemplateId: 1,
            entityType: 'invoice', entityId: 100,
            status: 'pending', currentStep: 0, approvalLog: null,
            requestedBy: 42,
        );
    }

    #[Test]
    public function constructor_sets_properties()
    {
        $this->assertSame(1, $this->instance->id);
        $this->assertSame('invoice', $this->instance->entityType);
        $this->assertSame('pending', $this->instance->status);
    }

    #[Test]
    public function advance_moves_to_next_step()
    {
        $advanced = $this->instance->advance('approve', 10, 3);

        $this->assertSame('in_progress', $advanced->status);
        $this->assertSame(1, $advanced->currentStep);
        $this->assertCount(1, $advanced->approvalLog);
    }

    #[Test]
    public function advance_completes_on_last_step()
    {
        $instance = new WorkflowInstance(id: 1, businessId: 5, workflowTemplateId: 1, entityType: 'invoice', entityId: 100, status: 'in_progress', currentStep: 2, approvalLog: null, requestedBy: 42);
        $advanced = $instance->advance('approve', 10, 3);

        $this->assertSame('approved', $advanced->status);
        $this->assertNotNull($advanced->completedAt);
    }

    #[Test]
    public function reject_sets_status()
    {
        $rejected = $this->instance->reject(10, 'Not needed');

        $this->assertSame('rejected', $rejected->status);
        $this->assertCount(1, $rejected->approvalLog);
    }

    #[Test]
    public function cannot_reject_already_completed()
    {
        $completed = new WorkflowInstance(id: 1, businessId: 5, workflowTemplateId: 1, entityType: 'invoice', entityId: 100, status: 'approved', currentStep: 3, approvalLog: null, requestedBy: 42);

        $this->expectException(\DomainException::class);
        $completed->reject(10, 'No');
    }

    #[Test]
    public function escalate_sets_status()
    {
        $escalated = $this->instance->escalate();

        $this->assertSame('escalated', $escalated->status);
    }

    #[Test]
    public function cannot_escalate_completed_workflow()
    {
        $completed = new WorkflowInstance(id: 1, businessId: 5, workflowTemplateId: 1, entityType: 'invoice', entityId: 100, status: 'approved', currentStep: 3, approvalLog: null, requestedBy: 42);

        $this->expectException(\DomainException::class);
        $completed->escalate();
    }

    #[Test]
    public function cancel_sets_status()
    {
        $cancelled = $this->instance->cancel();

        $this->assertSame('cancelled', $cancelled->status);
    }

    #[Test]
    public function cannot_cancel_completed_workflow()
    {
        $completed = new WorkflowInstance(id: 1, businessId: 5, workflowTemplateId: 1, entityType: 'invoice', entityId: 100, status: 'rejected', currentStep: 1, approvalLog: null, requestedBy: 42);

        $this->expectException(\DomainException::class);
        $completed->cancel();
    }

    #[Test]
    public function cannot_advance_non_active_workflow()
    {
        $rejected = $this->instance->reject(1, 'No');

        $this->expectException(\DomainException::class);
        $rejected->advance('approve', 2, 3);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $wf = WorkflowInstance::reconstitute([
            'id' => 1, 'business_id' => 5, 'workflow_template_id' => 1,
            'entity_type' => 'invoice', 'entity_id' => 100,
            'requested_by' => 42, 'status' => 'in_progress',
        ]);

        $this->assertSame('in_progress', $wf->status);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $array = $this->instance->toArray();

        $this->assertSame('invoice', $array['entity_type']);
        $this->assertSame('pending', $array['status']);
    }
}
