<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\OrgGroup;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OrgGroupTest extends TestCase
{
    #[Test]
    public function create_returns_new_instance()
    {
        $group = OrgGroup::create(parentOrgId: 1, childOrgId: 2);

        $this->assertNull($group->id);
        $this->assertSame(1, $group->parentOrgId);
        $this->assertSame(2, $group->childOrgId);
        $this->assertSame('subsidiary', $group->relationshipType);
        $this->assertTrue($group->isActive);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $group = OrgGroup::reconstitute([
            'id' => 1, 'parent_org_id' => 1, 'child_org_id' => 2,
            'relationship_type' => 'branch', 'is_active' => true,
        ]);

        $this->assertSame(1, $group->id);
        $this->assertSame('branch', $group->relationshipType);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $group = OrgGroup::create(parentOrgId: 1, childOrgId: 2, relationshipType: 'subsidiary');
        $array = $group->toArray();

        $this->assertSame(1, $array['parent_org_id']);
        $this->assertSame('subsidiary', $array['relationship_type']);
    }
}
