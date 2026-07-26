<?php

namespace App\Domain\Core\Contracts;

use App\Domain\Core\Enums\HealthStatus;

interface HealthService
{
    public function check(?string $applicationId = null): HealthStatus;

    public function isHealthy(?string $applicationId = null): bool;

    public function details(?string $applicationId = null): array;

    public function all(): array;
}
