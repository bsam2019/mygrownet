<?php

namespace App\Domain\GrowStream\Services;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\PlatformGateway;

class TenantPaymentResolver
{
    /**
     * Resolve tenant-scoped payment gateway credentials.
     */
    public function resolveTenantGateway(int $organizationId, string $gatewaySlug): ?array
    {
        $gateway = PlatformGateway::where('organization_id', $organizationId)
            ->where('gateway_slug', $gatewaySlug)
            ->where('is_active', true)
            ->first();

        if (!$gateway) {
            return null;
        }

        return $gateway->credentials;
    }

    /**
     * Store or update tenant payment gateway credentials (BYOP).
     */
    public function saveTenantGateway(int $organizationId, string $gatewaySlug, array $credentials): PlatformGateway
    {
        $gateway = PlatformGateway::firstOrNew([
            'organization_id' => $organizationId,
            'gateway_slug' => $gatewaySlug,
        ]);

        $gateway->credentials = $credentials;
        $gateway->is_active = true;
        $gateway->save();

        return $gateway;
    }
}
