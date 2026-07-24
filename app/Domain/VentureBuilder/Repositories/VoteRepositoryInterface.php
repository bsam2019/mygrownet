<?php

declare(strict_types=1);

namespace App\Domain\VentureBuilder\Repositories;

use App\Domain\VentureBuilder\Entities\Vote;

interface VoteRepositoryInterface
{
    public function findByResolutionAndShareholder(int $resolutionId, int $shareholderId): ?Vote;
    public function save(Vote $vote): Vote;
}
