<?php

namespace App\Domain\GrowStream\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LmsIntegrationService
{
    /**
     * Generate or fetch Moodle LMS API token for an organization.
     */
    public function getOrCreateApiToken(int $organizationId, ?string $moodleUrl = null): string
    {
        try {
            $existing = DB::table('growstream_lms_tokens')
                ->where('organization_id', $organizationId)
                ->where('is_active', true)
                ->first();

            if ($existing) {
                return $existing->api_token;
            }

            $token = 'gslms_' . Str::random(40);

            DB::table('growstream_lms_tokens')->insert([
                'organization_id' => $organizationId,
                'api_token' => $token,
                'moodle_url' => $moodleUrl,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $token;
        } catch (\Exception $e) {
            return 'gslms_' . Str::random(40);
        }
    }

    /**
     * Validate an incoming Moodle LMS request token.
     */
    public function validateToken(string $token): ?int
    {
        try {
            $record = DB::table('growstream_lms_tokens')
                ->where('api_token', $token)
                ->where('is_active', true)
                ->first();

            return $record?->organization_id ? (int) $record->organization_id : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
