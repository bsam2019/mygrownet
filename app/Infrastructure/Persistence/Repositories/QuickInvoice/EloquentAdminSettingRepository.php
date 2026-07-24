<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\QuickInvoice;

use App\Domain\QuickInvoice\Repositories\AdminSettingRepositoryInterface;
use App\Models\QuickInvoice\AdminSetting;

class EloquentAdminSettingRepository implements AdminSettingRepositoryInterface
{
    public function get(string $key, mixed $default = null): mixed
    {
        return AdminSetting::get($key, $default);
    }

    public function set(string $key, mixed $value, ?int $updatedBy = null): void
    {
        AdminSetting::set($key, $value, $updatedBy);
    }

    public function isUsageLimitsEnabled(): bool
    {
        return AdminSetting::isUsageLimitsEnabled();
    }

    public function setUsageLimitsEnabled(bool $enabled, ?int $updatedBy = null): void
    {
        AdminSetting::setUsageLimitsEnabled($enabled, $updatedBy);
    }

    public function getMonetizationSettings(): array
    {
        return AdminSetting::getMonetizationSettings();
    }

    public function updateMonetizationSettings(array $settings, ?int $updatedBy = null): void
    {
        AdminSetting::updateMonetizationSettings($settings, $updatedBy);
    }
}