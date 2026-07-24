<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\Repositories;

use App\Domain\GrowNet\Entities\StarterKit;
use App\Domain\GrowNet\ValueObjects\MemberId;

interface StarterKitRepositoryInterface
{
    public function findByMemberId(MemberId $memberId): ?StarterKit;
    public function save(StarterKit $starterKit): StarterKit;
}
