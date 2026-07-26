<?php

namespace App\Domain\Platform\Contracts;

interface AuditService
{
    public function log(string $action, array $data, ?string $modelType = null, ?string $modelId = null): void;
    public function query(array $filters = [], int $perPage = 50): array;
    public function export(\DateTimeImmutable $from, \DateTimeImmutable $to, string $format = 'csv'): string;
}
