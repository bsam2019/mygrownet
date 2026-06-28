<?php

namespace App\Domain\EmailMarketing\Repositories;

use App\Domain\EmailMarketing\Entities\EmailCampaign;
use App\Domain\EmailMarketing\ValueObjects\CampaignId;
use App\Domain\EmailMarketing\ValueObjects\CampaignType;
use App\Domain\EmailMarketing\ValueObjects\CampaignStatus;

interface EmailCampaignRepository
{
    public function save(EmailCampaign $campaign): void;
    
    public function findById(CampaignId $id): ?EmailCampaign;
    
    public function findByType(CampaignType $type): array;
    
    public function findByStatus(CampaignStatus $status): array;
    
    public function findActiveByType(CampaignType $type): ?EmailCampaign;
    
    public function delete(CampaignId $id): void;
    
    public function all(): array;
}
