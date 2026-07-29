<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\TeamMember;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TeamMemberTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $member = new TeamMember(
            id: 1, businessId: 5, userId: 42, role: 'admin',
            permissions: ['view_reports', 'manage_invoices'],
            status: 'active', invitationToken: null, invitedAt: null,
            acceptedAt: null, createdAt: null, updatedAt: null,
        );

        $this->assertSame(1, $member->id);
        $this->assertSame('admin', $member->role);
        $this->assertSame(['view_reports', 'manage_invoices'], $member->permissions);
    }

    #[Test]
    public function is_owner_returns_true_for_owner_role()
    {
        $member = new TeamMember(id: 1, businessId: 5, userId: 42, role: 'owner', permissions: null, status: 'active', invitationToken: null, invitedAt: null, acceptedAt: null, createdAt: null, updatedAt: null);
        $this->assertTrue($member->isOwner());
    }

    #[Test]
    public function is_owner_returns_false_for_non_owner()
    {
        $member = new TeamMember(id: 1, businessId: 5, userId: 42, role: 'admin', permissions: null, status: 'active', invitationToken: null, invitedAt: null, acceptedAt: null, createdAt: null, updatedAt: null);
        $this->assertFalse($member->isOwner());
    }

    #[Test]
    public function is_admin_returns_true_for_admin_role()
    {
        $member = new TeamMember(id: 1, businessId: 5, userId: 42, role: 'admin', permissions: null, status: 'active', invitationToken: null, invitedAt: null, acceptedAt: null, createdAt: null, updatedAt: null);
        $this->assertTrue($member->isAdmin());
    }

    #[Test]
    public function has_permission_returns_true_for_owner()
    {
        $member = new TeamMember(id: 1, businessId: 5, userId: 42, role: 'owner', permissions: [], status: 'active', invitationToken: null, invitedAt: null, acceptedAt: null, createdAt: null, updatedAt: null);
        $this->assertTrue($member->hasPermission('anything'));
    }

    #[Test]
    public function has_permission_checks_against_permissions_array()
    {
        $member = new TeamMember(id: 1, businessId: 5, userId: 42, role: 'member', permissions: ['read'], status: 'active', invitationToken: null, invitedAt: null, acceptedAt: null, createdAt: null, updatedAt: null);
        $this->assertTrue($member->hasPermission('read'));
        $this->assertFalse($member->hasPermission('write'));
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $member = TeamMember::reconstitute([
            'id' => 1, 'business_id' => 5, 'user_id' => 42,
            'role' => 'admin', 'status' => 'active',
        ]);

        $this->assertSame('admin', $member->role);
        $this->assertSame('active', $member->status);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $member = new TeamMember(id: 1, businessId: 5, userId: 42, role: 'admin', permissions: ['read'], status: 'active', invitationToken: null, invitedAt: null, acceptedAt: null, createdAt: null, updatedAt: null);
        $array = $member->toArray();

        $this->assertSame('admin', $array['role']);
        $this->assertSame(['read'], $array['permissions']);
    }
}
