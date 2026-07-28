<?php

namespace Tests\Feature\GrowNet;

use App\Domain\GrowNet\Entities\Member;
use App\Domain\GrowNet\Entities\TierUpgrade;
use App\Domain\GrowNet\Exceptions\TierUpgradeException;
use App\Domain\GrowNet\ValueObjects\MembershipTier;
use App\Infrastructure\Persistence\Eloquent\GrowNet\MemberModel;

class TierAdvancementServiceTest extends GrowNetTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'mygrownet.tier_advancement_bonuses' => [
                'Bronze' => 100,
                'Silver' => 250,
                'Gold' => 500,
                'Diamond' => 1000,
                'Elite' => 2500,
            ],
        ]);
    }

    public function test_upgrade_member_from_associate_to_bronze(): void
    {
        $member = $this->memberService->getOrCreateMember($this->user->id);

        $upgrade = $this->tierAdvancementService->upgradeMember(
            $member,
            MembershipTier::Bronze,
            'manual',
        );

        $this->assertInstanceOf(TierUpgrade::class, $upgrade);
        $this->assertEquals(MembershipTier::Associate, $upgrade->fromTier());
        $this->assertEquals(MembershipTier::Bronze, $upgrade->toTier());
        $this->assertEquals('manual', $upgrade->reason());
        $this->assertEquals(100, $upgrade->achievementBonus()->amount());
    }

    public function test_upgrade_member_from_bronze_to_silver(): void
    {
        $member = $this->memberService->getOrCreateMember($this->user->id);

        $this->tierAdvancementService->upgradeMember($member, MembershipTier::Bronze);
        $member = $this->memberService->getMember($this->user->id);

        $upgrade = $this->tierAdvancementService->upgradeMember(
            $member,
            MembershipTier::Silver,
            'team_volume_met',
        );

        $this->assertEquals(MembershipTier::Bronze, $upgrade->fromTier());
        $this->assertEquals(MembershipTier::Silver, $upgrade->toTier());
        $this->assertEquals('team_volume_met', $upgrade->reason());
        $this->assertEquals(250, $upgrade->achievementBonus()->amount());
    }

    public function test_upgrade_member_to_same_tier_throws(): void
    {
        $member = $this->memberService->getOrCreateMember($this->user->id);
        $this->tierAdvancementService->upgradeMember($member, MembershipTier::Bronze);
        $member = $this->memberService->getMember($this->user->id);

        $this->expectException(TierUpgradeException::class);
        $this->tierAdvancementService->upgradeMember($member, MembershipTier::Bronze);
    }

    public function test_upgrade_member_to_elite(): void
    {
        $member = $this->memberService->getOrCreateMember($this->user->id);

        $this->tierAdvancementService->upgradeMember($member, MembershipTier::Bronze);
        $member = $this->memberService->getMember($this->user->id);
        $this->tierAdvancementService->upgradeMember($member, MembershipTier::Silver);
        $member = $this->memberService->getMember($this->user->id);
        $this->tierAdvancementService->upgradeMember($member, MembershipTier::Gold);
        $member = $this->memberService->getMember($this->user->id);
        $this->tierAdvancementService->upgradeMember($member, MembershipTier::Diamond);
        $member = $this->memberService->getMember($this->user->id);

        $upgrade = $this->tierAdvancementService->upgradeMember(
            $member,
            MembershipTier::Elite,
            'exceptional_performance',
        );

        $this->assertEquals(MembershipTier::Diamond, $upgrade->fromTier());
        $this->assertEquals(MembershipTier::Elite, $upgrade->toTier());
        $this->assertEquals('exceptional_performance', $upgrade->reason());
        $this->assertEquals(2500, $upgrade->achievementBonus()->amount());
    }

    public function test_calculate_tier_progress_from_associate_to_bronze(): void
    {
        $member = $this->memberService->getOrCreateMember($this->user->id);

        $progress = $this->tierAdvancementService->calculateTierProgress(
            $member,
            MembershipTier::Associate,
            MembershipTier::Bronze,
        );

        $this->assertEquals(100, $progress);
    }

    public function test_tier_upgrade_persisted_to_database(): void
    {
        $member = $this->memberService->getOrCreateMember($this->user->id);
        $upgrade = $this->tierAdvancementService->upgradeMember(
            $member,
            MembershipTier::Bronze,
        );

        $this->assertDatabaseHas('tier_upgrades', [
            'user_id' => $member->id()->value(),
            'upgrade_reason' => 'manual',
        ]);
    }

    public function test_process_automatic_upgrades_with_no_eligible_members(): void
    {
        $result = $this->tierAdvancementService->processAutomaticUpgrades();

        $this->assertArrayHasKey('processed', $result);
        $this->assertArrayHasKey('failed', $result);
        $this->assertEmpty($result['processed']);
        $this->assertEmpty($result['failed']);
    }
}
