<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\IntercompanyTransaction;

interface IntercompanyTransactionRepositoryInterface
{
    public function findById(int $id): ?IntercompanyTransaction;

    /** @return IntercompanyTransaction[] */
    public function findByOrg(int $orgId): array;

    /** @return IntercompanyTransaction[] */
    public function findByFromOrg(int $fromOrgId): array;

    /** @return IntercompanyTransaction[] */
    public function findByToOrg(int $toOrgId): array;

    /** @return IntercompanyTransaction[] */
    public function findPending(): array;

    /** @return IntercompanyTransaction[] */
    public function findUnmatched(): array;

    /** @return IntercompanyTransaction[] */
    public function findMatched(): array;

    /** @return IntercompanyTransaction[] */
    public function findByStatus(string $status): array;

    public function save(IntercompanyTransaction $tx): IntercompanyTransaction;

    public function delete(int $id): void;
}
