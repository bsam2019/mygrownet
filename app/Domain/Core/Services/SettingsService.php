<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Models\AppSetting;
use App\Domain\Core\ValueObjects\PlatformContext;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    private const CACHE_TTL = 3600;

    public function __construct(
        private PlatformContextResolver $contextResolver,
    ) {}

    public function get(string $key, mixed $default = null, ?int $organizationId = null, ?string $module = null): mixed
    {
        $orgId = $organizationId ?? $this->resolveOrganizationId();
        $cacheKey = "settings.{$orgId}.{$module}.{$key}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $orgId, $module, $default) {
            $query = AppSetting::where('key', $key);

            if ($orgId) {
                $query->where(function ($q) use ($orgId) {
                    $q->where('organization_id', $orgId)
                      ->orWhereNull('organization_id');
                })->orderBy('organization_id', 'desc');
            } else {
                $query->whereNull('organization_id');
            }

            if ($module) {
                $query->where('module', $module);
            }

            $setting = $query->first();

            if (!$setting) {
                return $default;
            }

            return $this->castValue($setting->value, $setting->type);
        });
    }

    public function set(string $key, mixed $value, ?int $organizationId = null, ?string $module = null, string $type = 'string'): void
    {
        $orgId = $organizationId ?? $this->resolveOrganizationId();

        AppSetting::updateOrCreate(
            ['key' => $key, 'organization_id' => $orgId, 'module' => $module],
            ['value' => (string) $value, 'type' => $type]
        );

        Cache::forget("settings.{$orgId}.{$module}.{$key}");
    }

    public function delete(string $key, ?int $organizationId = null, ?string $module = null): void
    {
        $orgId = $organizationId ?? $this->resolveOrganizationId();

        AppSetting::where('key', $key)
            ->where('organization_id', $orgId)
            ->where('module', $module)
            ->delete();

        Cache::forget("settings.{$orgId}.{$module}.{$key}");
    }

    public function all(?int $organizationId = null, ?string $module = null): array
    {
        $orgId = $organizationId ?? $this->resolveOrganizationId();
        $query = AppSetting::query();

        if ($orgId) {
            $query->where(function ($q) use ($orgId) {
                $q->where('organization_id', $orgId)
                  ->orWhereNull('organization_id');
            });
        }

        if ($module) {
            $query->where('module', $module);
        }

        return $query->orderBy('key')->get()->toArray();
    }

    private function resolveOrganizationId(): ?int
    {
        $context = $this->contextResolver->current();
        return $context && $context->organizationId ? (int) $context->organizationId : null;
    }

    private function castValue(string $value, string $type): mixed
    {
        return match ($type) {
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int' => (int) $value,
            'float', 'double' => (float) $value,
            'json' => json_decode($value, true),
            'string' => $value,
            default => $value,
        };
    }
}
