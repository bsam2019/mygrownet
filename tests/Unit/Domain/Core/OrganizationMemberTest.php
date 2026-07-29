<?php

namespace Tests\Unit\Domain\Core;

use App\Domain\Core\Entities\OrganizationMember;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OrganizationMemberTest extends TestCase
{
    #[Test]
    public function constructor_sets_all_properties()
    {
        $joined = new DateTimeImmutable('2026-01-15');
        $created = new DateTimeImmutable('2026-01-15');
        $updated = new DateTimeImmutable('2026-06-01');

        $member = new OrganizationMember(
            id: '1',
            organizationId: 'org-1',
            userId: 'user-42',
            role: 'admin',
            status: 'active',
            permissions: ['manage_users', 'view_reports'],
            joinedAt: $joined,
            createdAt: $created,
            updatedAt: $updated,
        );

        $this->assertEquals('1', $member->id);
        $this->assertEquals('org-1', $member->organizationId);
        $this->assertEquals('user-42', $member->userId);
        $this->assertEquals('admin', $member->role);
        $this->assertEquals('active', $member->status);
        $this->assertEquals(['manage_users', 'view_reports'], $member->permissions);
        $this->assertSame($joined, $member->joinedAt);
        $this->assertSame($created, $member->createdAt);
        $this->assertSame($updated, $member->updatedAt);
    }

    #[Test]
    public function constructor_accepts_null_updatedAt()
    {
        $now = new DateTimeImmutable();

        $member = new OrganizationMember(
            id: '2',
            organizationId: 'org-1',
            userId: 'user-1',
            role: 'member',
            status: 'active',
            permissions: [],
            joinedAt: $now,
            createdAt: $now,
            updatedAt: null,
        );

        $this->assertNull($member->updatedAt);
    }
}
