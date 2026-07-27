<?php

namespace App\Domain\PlatformBilling\Repositories;

use App\Domain\PlatformBilling\Entities\Invoice;

interface InvoiceRepositoryInterface
{
    public function findById(int $id): ?Invoice;
    public function findBySubscription(int $subscriptionId): array;
    public function findByOrganization(int $organizationId): array;
    public function findOverdue(): array;
    public function findDueToday(): array;
    public function save(Invoice $invoice): Invoice;
    public function delete(int $id): void;
}
