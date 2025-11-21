<?php

namespace App\Application\UseCases\EmailMarketing;

use App\Domain\EmailMarketing\Repositories\EmailCampaignRepository;
use App\Domain\EmailMarketing\ValueObjects\CampaignId;

class ActivateCampaignUseCase
{
    public function __construct(
        private EmailCampaignRepository $campaignRepository
    ) {}

    public function execute(int $campaignId): void
    {
        $campaign = $this->campaignRepository->findById(CampaignId::fromInt($campaignId));

        if (!$campaign) {
            throw new \DomainException('Campaign not found');
        }

        $campaign->activate();

        $this->campaignRepository->save($campaign);
    }
}
