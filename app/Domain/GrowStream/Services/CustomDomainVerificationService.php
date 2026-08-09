<?php

namespace App\Domain\GrowStream\Services;

class CustomDomainVerificationService
{
    /**
     * Verify CNAME DNS configuration for custom domain target.
     */
    public function verifyCnameRecord(string $domain, string $expectedTarget = 'cname.growstream.app'): array
    {
        $domain = strtolower(trim($domain));

        if (empty($domain)) {
            return [
                'status' => 'invalid',
                'message' => 'Domain name cannot be empty.',
            ];
        }

        // Perform DNS CNAME record lookup
        try {
            $records = dns_get_record($domain, DNS_CNAME);
            $hasCname = false;

            if ($records && is_array($records)) {
                foreach ($records as $r) {
                    if (isset($r['target']) && (str_contains($r['target'], 'growstream') || str_contains($r['target'], 'mygrownet'))) {
                        $hasCname = true;
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            $hasCname = false;
        }

        if ($hasCname) {
            return [
                'status' => 'verified',
                'message' => "CNAME record for {$domain} is verified and pointing to GrowStream Hub.",
                'ssl_status' => 'active',
            ];
        }

        return [
            'status' => 'pending_dns',
            'message' => "CNAME record not detected yet. Ensure your DNS provider has a CNAME record mapping {$domain} to {$expectedTarget}.",
            'ssl_status' => 'pending',
        ];
    }
}
