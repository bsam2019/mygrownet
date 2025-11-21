<?php

namespace App\Application\Services;

use App\Application\UseCases\EmailMarketing\CreateCampaignUseCase;
use App\Application\UseCases\EmailMarketing\EnrollUserInCampaignUseCase;
use App\Application\UseCases\EmailMarketing\ActivateCampaignUseCase;
use App\Domain\EmailMarketing\Repositories\EmailCampaignRepository;
use App\Domain\EmailMarketing\ValueObjects\CampaignId;
use App\Infrastructure\Persistence\Eloquent\EmailMarketing\CampaignAnalyticsModel;
use App\Models\User;

class EmailCampaignService
{
    public function __construct(
        private EmailCampaignRepository $campaignRepository,
        private CreateCampaignUseCase $createCampaignUseCase,
        private EnrollUserInCampaignUseCase $enrollUserUseCase,
        private ActivateCampaignUseCase $activateCampaignUseCase
    ) {}

    public function createCampaign(array $data): array
    {
        $campaign = $this->createCampaignUseCase->execute($data);

        return [
            'id' => $campaign->id()->value(),
            'name' => $campaign->name(),
            'type' => $campaign->type()->value,
            'status' => $campaign->status()->value,
        ];
    }

    public function enrollUser(int $campaignId, User $user): void
    {
        $campaign = $this->campaignRepository->findById(CampaignId::fromInt($campaignId));

        if (!$campaign) {
            throw new \DomainException('Campaign not found');
        }

        $this->enrollUserUseCase->execute($campaign, $user);
    }

    public function activateCampaign(int $campaignId): void
    {
        $this->activateCampaignUseCase->execute($campaignId);
    }

    public function pauseCampaign(int $campaignId): void
    {
        $campaign = $this->campaignRepository->findById(CampaignId::fromInt($campaignId));

        if (!$campaign) {
            throw new \DomainException('Campaign not found');
        }

        $campaign->pause();
        $this->campaignRepository->save($campaign);
    }

    public function resumeCampaign(int $campaignId): void
    {
        $campaign = $this->campaignRepository->findById(CampaignId::fromInt($campaignId));

        if (!$campaign) {
            throw new \DomainException('Campaign not found');
        }

        $campaign->resume();
        $this->campaignRepository->save($campaign);
    }

    public function getCampaignStats(int $campaignId): array
    {
        $analytics = CampaignAnalyticsModel::where('campaign_id', $campaignId)
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        $totals = CampaignAnalyticsModel::where('campaign_id', $campaignId)
            ->selectRaw('
                SUM(emails_sent) as total_sent,
                SUM(emails_delivered) as total_delivered,
                SUM(emails_opened) as total_opened,
                SUM(emails_clicked) as total_clicked,
                SUM(conversions) as total_conversions,
                SUM(revenue) as total_revenue
            ')
            ->first();

        return [
            'totals' => [
                'sent' => $totals->total_sent ?? 0,
                'delivered' => $totals->total_delivered ?? 0,
                'opened' => $totals->total_opened ?? 0,
                'clicked' => $totals->total_clicked ?? 0,
                'conversions' => $totals->total_conversions ?? 0,
                'revenue' => $totals->total_revenue ?? 0,
                'open_rate' => $totals->total_delivered > 0 
                    ? round(($totals->total_opened / $totals->total_delivered) * 100, 2) 
                    : 0,
                'click_rate' => $totals->total_delivered > 0 
                    ? round(($totals->total_clicked / $totals->total_delivered) * 100, 2) 
                    : 0,
                'conversion_rate' => $totals->total_delivered > 0 
                    ? round(($totals->total_conversions / $totals->total_delivered) * 100, 2) 
                    : 0,
            ],
            'daily' => $analytics->map(fn($a) => [
                'date' => $a->date->format('Y-m-d'),
                'sent' => $a->emails_sent,
                'opened' => $a->emails_opened,
                'clicked' => $a->emails_clicked,
                'open_rate' => $a->open_rate,
                'click_rate' => $a->click_rate,
            ])->toArray(),
        ];
    }
}
