<?php

namespace App\Application\UseCases\EmailMarketing;

use App\Domain\EmailMarketing\Entities\EmailCampaign;
use App\Domain\EmailMarketing\Repositories\EmailCampaignRepository;
use App\Domain\EmailMarketing\ValueObjects\CampaignType;
use App\Domain\EmailMarketing\ValueObjects\TriggerType;

class CreateCampaignUseCase
{
    public function __construct(
        private EmailCampaignRepository $campaignRepository
    ) {}

    public function execute(array $data): EmailCampaign
    {
        $campaign = EmailCampaign::create(
            name: $data['name'],
            type: CampaignType::from($data['type']),
            triggerType: TriggerType::from($data['trigger_type']),
            triggerConfig: $data['trigger_config'] ?? null,
            createdBy: $data['created_by'] ?? null
        );

        $this->campaignRepository->save($campaign);

        return $campaign;
    }
}
