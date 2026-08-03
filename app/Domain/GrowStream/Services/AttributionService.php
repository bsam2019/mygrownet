<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Services;

use App\Domain\GrowStream\Repositories\AttributionLinkRepositoryInterface;
use Illuminate\Support\Str;

/**
 * Silent attribution tracking for creator shareable links.
 *
 * Phase 1 is tracking-only: source + visitor session + eventual conversion +
 * watch minutes are collected from day one but never surfaced to creators.
 * Payout/compensation mechanics are a Phase 3 decision.
 */
class AttributionService
{
    public function __construct(
        private AttributionLinkRepositoryInterface $attributionRepo,
    ) {}

    public function resolve(int $creatorId, ?string $source, ?string $visitorSessionId = null): array
    {
        $session = $visitorSessionId ?: $this->newSessionId();

        // One row per session; re-resolving the same session doesn't duplicate.
        $existing = $this->attributionRepo->findBySession($session);
        if ($existing !== null) {
            return $existing->toArray();
        }

        $link = $this->attributionRepo->save([
            'uuid' => (string) Str::uuid(),
            'creator_id' => $creatorId,
            'source' => $this->sanitizeSource($source),
            'visitor_session_id' => $session,
            'converted_user_id' => null,
            'watch_minutes_attributed' => 0,
        ]);

        return $link->toArray();
    }

    public function recordConversion(string $visitorSessionId, int $userId): void
    {
        $this->attributionRepo->bindConversion($visitorSessionId, $userId);
    }

    public function accumulateWatchMinutes(string $visitorSessionId, int $minutes): void
    {
        $this->attributionRepo->accumulateWatchMinutes($visitorSessionId, $minutes);
    }

    public function newSessionId(): string
    {
        return (string) Str::uuid();
    }

    private function sanitizeSource(?string $source): ?string
    {
        if ($source === null || trim($source) === '') {
            return null;
        }

        $clean = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $source);

        return $clean === '' || $clean === null ? null : substr($clean, 0, 60);
    }
}
