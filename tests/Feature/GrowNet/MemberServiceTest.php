<?php

namespace Tests\Feature\GrowNet;

use App\Models\User;
use App\Domain\GrowNet\Entities\Member;
use App\Domain\GrowNet\Exceptions\MemberNotFoundException;
use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Infrastructure\Persistence\Eloquent\GrowNet\MemberModel;

class MemberServiceTest extends GrowNetTestCase
{
    public function test_get_or_create_member_creates_new_member(): void
    {
        $member = $this->memberService->getOrCreateMember($this->user->id);

        $this->assertInstanceOf(Member::class, $member);
        $this->assertEquals($this->user->id, $member->userId());
        $this->assertGreaterThan(0, $member->id()->value());
    }

    public function test_get_or_create_member_returns_existing_member(): void
    {
        $first = $this->memberService->getOrCreateMember($this->user->id);
        $second = $this->memberService->getOrCreateMember($this->user->id);

        $this->assertEquals($first->id()->value(), $second->id()->value());
    }

    public function test_get_member_by_user_id(): void
    {
        $created = $this->memberService->getOrCreateMember($this->user->id);
        $found = $this->memberService->getMember($this->user->id);

        $this->assertEquals($created->id()->value(), $found->id()->value());
        $this->assertEquals($this->user->id, $found->userId());
    }

    public function test_get_member_throws_not_found(): void
    {
        $this->expectException(MemberNotFoundException::class);
        $this->memberService->getMember(99999);
    }

    public function test_get_member_by_id(): void
    {
        $created = $this->memberService->getOrCreateMember($this->user->id);
        $found = $this->memberService->getMemberById(new MemberId($created->id()->value()));

        $this->assertEquals($created->id()->value(), $found->id()->value());
    }

    public function test_get_member_by_id_throws_not_found(): void
    {
        $this->expectException(MemberNotFoundException::class);
        $this->memberService->getMemberById(new MemberId(99999));
    }

    public function test_get_or_create_member_with_referrer(): void
    {
        $referrerUser = User::factory()->create();
        $referrer = $this->memberService->getOrCreateMember($referrerUser->id);

        $member = $this->memberService->getOrCreateMember(
            userId: $this->user->id,
            referrerId: $referrer->id()->value(),
            referralCode: 'TESTCODE',
        );

        $this->assertEquals($referrer->id()->value(), $member->referrerId()?->value());
        $this->assertEquals('TESTCODE', $member->referralCode()?->value());
    }

    public function test_get_member_stats_after_creation(): void
    {
        $member = $this->memberService->getOrCreateMember($this->user->id);
        $stats = $this->memberService->getMemberStats($member);

        $this->assertArrayHasKey('total_referrals', $stats);
        $this->assertArrayHasKey('active_referrals', $stats);
        $this->assertArrayHasKey('team_volume', $stats);
        $this->assertArrayHasKey('network_size', $stats);
        $this->assertArrayHasKey('total_earnings', $stats);
        $this->assertArrayHasKey('balance', $stats);
        $this->assertArrayHasKey('current_tier', $stats);
        $this->assertEquals(0, $stats['total_referrals']);
        $this->assertEquals(0, $stats['active_referrals']);
        $this->assertEquals(0, $stats['team_volume']);
        $this->assertFalse($stats['has_starter_kit']);
    }

    public function test_get_leaderboard_returns_empty_when_no_members(): void
    {
        $leaderboard = $this->memberService->getLeaderboard();
        $this->assertIsArray($leaderboard);
    }

    public function test_get_eligible_for_tier_upgrade_returns_empty_when_no_members(): void
    {
        $eligible = $this->memberService->getEligibleForTierUpgrade();
        $this->assertIsArray($eligible);
        $this->assertEmpty($eligible);
    }

    public function test_member_persisted_to_database(): void
    {
        $this->memberService->getOrCreateMember($this->user->id);

        $this->assertDatabaseHas('grow_net_users', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_member_persisted_with_referrer(): void
    {
        $referrerUser = User::factory()->create();
        $referrer = $this->memberService->getOrCreateMember($referrerUser->id);

        $this->memberService->getOrCreateMember(
            userId: $this->user->id,
            referrerId: $referrer->id()->value(),
            referralCode: 'REFME',
        );

        $this->assertDatabaseHas('grow_net_users', [
            'user_id' => $this->user->id,
            'referrer_id' => $referrer->id()->value(),
            'referral_code' => 'REFME',
        ]);
    }
}
