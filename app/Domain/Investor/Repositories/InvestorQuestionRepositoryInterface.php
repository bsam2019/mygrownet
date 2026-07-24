<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\InvestorQuestion;

interface InvestorQuestionRepositoryInterface
{
    public function save(InvestorQuestion $question): InvestorQuestion;

    public function findById(int $id): ?InvestorQuestion;

    public function findByInvestor(int $investorAccountId): array;

    public function findPublished(int $limit = 20): array;

    public function findFeatured(int $limit = 5): array;

    public function findByCategory(string $category): array;

    public function findPublicByCategory(?string $category = null, int $perPage = 15): array;

    public function upvote(int $questionId, int $investorAccountId): bool;

    public function removeUpvote(int $questionId, int $investorAccountId): bool;

    public function incrementUpvotes(int $questionId): void;

    public function getCategoryCounts(): array;

    public function getPendingCount(): int;

    public function delete(int $id): bool;
}
