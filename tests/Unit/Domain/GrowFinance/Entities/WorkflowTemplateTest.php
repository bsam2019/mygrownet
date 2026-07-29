<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\WorkflowTemplate;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WorkflowTemplateTest extends TestCase
{
    private WorkflowTemplate $template;

    protected function setUp(): void
    {
        $this->template = new WorkflowTemplate(
            id: 1, businessId: 5, name: 'Invoice Approval',
            description: 'Standard invoice approval flow',
            entityType: 'invoice', steps: [],
            isActive: true, slaHours: 48, allowEscalation: true,
        );
    }

    #[Test]
    public function constructor_sets_properties()
    {
        $this->assertSame(1, $this->template->id);
        $this->assertSame('Invoice Approval', $this->template->name);
        $this->assertSame('invoice', $this->template->entityType);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $tpl = WorkflowTemplate::reconstitute([
            'id' => 1, 'business_id' => 5, 'name' => 'Approval',
            'entity_type' => 'invoice', 'is_active' => true,
        ]);

        $this->assertSame('Approval', $tpl->name);
        $this->assertTrue($tpl->isActive);
    }

    #[Test]
    public function reconstitute_with_steps()
    {
        $tpl = WorkflowTemplate::reconstitute([
            'id' => 1, 'business_id' => 5, 'name' => 'Approval',
            'entity_type' => 'invoice', 'is_active' => true,
            'steps' => [['step_order' => 1, 'role' => 'manager', 'approver_id' => null, 'action' => 'approve']],
        ]);

        $this->assertCount(1, $tpl->steps);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $array = $this->template->toArray();

        $this->assertSame('Invoice Approval', $array['name']);
        $this->assertSame('invoice', $array['entity_type']);
    }
}
