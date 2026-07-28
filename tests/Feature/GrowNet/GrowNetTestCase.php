<?php

namespace Tests\Feature\GrowNet;

use App\Models\User;
use App\Domain\GrowNet\Repositories\MemberRepositoryInterface;
use App\Domain\GrowNet\Repositories\CommissionRepositoryInterface;
use App\Domain\GrowNet\Repositories\ReferralRepositoryInterface;
use App\Domain\GrowNet\Repositories\TeamVolumeRepositoryInterface;
use App\Domain\GrowNet\Repositories\TierUpgradeRepositoryInterface;
use App\Domain\GrowNet\Repositories\LoyaltyPointsRepositoryInterface;
use App\Domain\GrowNet\Repositories\StarterKitRepositoryInterface;
use App\Domain\GrowNet\Services\MemberService;
use App\Domain\GrowNet\Services\DashboardService;
use App\Domain\GrowNet\Services\TierAdvancementService;
use App\Infrastructure\Persistence\Eloquent\GrowNet\MemberModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class GrowNetTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected ?MemberModel $memberModel;

    protected MemberService $memberService;
    protected DashboardService $dashboardService;
    protected TierAdvancementService $tierAdvancementService;

    protected MemberRepositoryInterface $memberRepository;
    protected CommissionRepositoryInterface $commissionRepository;
    protected ReferralRepositoryInterface $referralRepository;
    protected TeamVolumeRepositoryInterface $teamVolumeRepository;
    protected TierUpgradeRepositoryInterface $tierUpgradeRepository;
    protected LoyaltyPointsRepositoryInterface $loyaltyPointsRepository;
    protected StarterKitRepositoryInterface $starterKitRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->user = User::factory()->create();

        $this->memberService = app(MemberService::class);
        $this->dashboardService = app(DashboardService::class);
        $this->tierAdvancementService = app(TierAdvancementService::class);

        $this->memberRepository = app(MemberRepositoryInterface::class);
        $this->commissionRepository = app(CommissionRepositoryInterface::class);
        $this->referralRepository = app(ReferralRepositoryInterface::class);
        $this->teamVolumeRepository = app(TeamVolumeRepositoryInterface::class);
        $this->tierUpgradeRepository = app(TierUpgradeRepositoryInterface::class);
        $this->loyaltyPointsRepository = app(LoyaltyPointsRepositoryInterface::class);
        $this->starterKitRepository = app(StarterKitRepositoryInterface::class);
    }
}
