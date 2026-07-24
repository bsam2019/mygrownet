<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Repositories;

interface AdminSettingRepositoryInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value, ?int $updatedBy = null): void;

    public function isUsageLimitsEnabled(): bool;

    public function setUsageLimitsEnabled(bool $enabled, ?int $updatedBy = null): void;

    public function getMonetizationSettings(): array;

    public function updateMonetizationSettings(array $settings, ?int $updatedBy = null): void;
}