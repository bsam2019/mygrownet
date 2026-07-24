<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\InvestorPollVote;

interface InvestorPollVoteRepositoryInterface
{
    public function save(InvestorPollVote $vote): InvestorPollVote;

    public function findById(int $id): ?InvestorPollVote;

    public function findByPollAndInvestor(int $pollId, int $investorAccountId): ?InvestorPollVote;

    public function findVotedPollIds(int $investorAccountId): array;
}
