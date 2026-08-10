<?php

namespace App\Services\GrowBuilder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PageRevisionService — automated page snapshot and visual rollback engine.
 *
 * Saves a JSON snapshot of a page's layout every time it is saved in the editor.
 * Supports one-click rollback to any previous revision (up to configurable limit).
 *
 * §34 of GROWBUILDER_PLATFORM.md
 */
class PageRevisionService
{
    private const DEFAULT_KEEP_REVISIONS = 20;
    private const DEFAULT_TTL_DAYS = 90;

    /**
     * Save a new revision snapshot for a page.
     * Automatically prunes old revisions beyond the keep limit.
     *
     * @param  int    $siteId
     * @param  int    $pageId
     * @param  array  $layoutJson     The complete section layout JSON for the page
     * @param  int    $userId         The user who made the change
     * @param  string $trigger        'manual', 'auto_save', 'pre_upgrade'
     * @param  string|null $message   Optional commit message
     * @return int    Revision number
     */
    public function saveRevision(
        int $siteId,
        int $pageId,
        array $layoutJson,
        int $userId,
        string $trigger = 'auto_save',
        ?string $message = null
    ): int {
        $nextRevision = $this->getNextRevisionNumber($siteId, $pageId);

        DB::table('growbuilder_page_revisions')->insert([
            'site_id'            => $siteId,
            'page_id'            => $pageId,
            'revision_number'    => $nextRevision,
            'layout_json'        => json_encode($layoutJson),
            'created_by_user_id' => $userId,
            'commit_message'     => $message,
            'trigger'            => $trigger,
            'expires_at'         => $trigger === 'pre_upgrade' ? null : now()->addDays(self::DEFAULT_TTL_DAYS),
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        // Prune old revisions beyond keep limit (preserve pre_upgrade snapshots)
        $this->pruneRevisions($siteId, $pageId);

        Log::info('GrowBuilder: page revision saved', [
            'site_id'  => $siteId,
            'page_id'  => $pageId,
            'revision' => $nextRevision,
            'trigger'  => $trigger,
        ]);

        return $nextRevision;
    }

    /**
     * List all revisions for a page (newest first).
     */
    public function listRevisions(int $siteId, int $pageId): array
    {
        return DB::table('growbuilder_page_revisions')
            ->where('site_id', $siteId)
            ->where('page_id', $pageId)
            ->orderByDesc('revision_number')
            ->select([
                'id', 'revision_number', 'created_by_user_id',
                'commit_message', 'trigger', 'created_at',
            ])
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();
    }

    /**
     * Get a specific revision's layout JSON.
     */
    public function getRevision(int $siteId, int $pageId, int $revisionNumber): ?array
    {
        $revision = DB::table('growbuilder_page_revisions')
            ->where('site_id', $siteId)
            ->where('page_id', $pageId)
            ->where('revision_number', $revisionNumber)
            ->first();

        if (!$revision) {
            return null;
        }

        return json_decode($revision->layout_json, true);
    }

    /**
     * Rollback a page to a specific revision.
     * Saves the current state as a new "pre_rollback" revision first.
     *
     * @return array The restored layout JSON, or null if revision not found
     */
    public function rollbackToRevision(
        int $siteId,
        int $pageId,
        int $revisionNumber,
        int $userId
    ): ?array {
        $targetLayout = $this->getRevision($siteId, $pageId, $revisionNumber);
        if (!$targetLayout) {
            return null;
        }

        // Get current live layout from pages table
        $currentPage = DB::table('growbuilder_pages')
            ->where('site_id', $siteId)
            ->where('id', $pageId)
            ->first();

        if ($currentPage) {
            $currentLayout = json_decode($currentPage->sections ?? '[]', true);
            $this->saveRevision($siteId, $pageId, $currentLayout, $userId, 'auto_save', 'Pre-rollback snapshot');
        }

        // Apply rollback: update the live page
        DB::table('growbuilder_pages')
            ->where('site_id', $siteId)
            ->where('id', $pageId)
            ->update([
                'sections'   => json_encode($targetLayout),
                'updated_at' => now(),
            ]);

        Log::info('GrowBuilder: page rolled back', [
            'site_id'  => $siteId,
            'page_id'  => $pageId,
            'revision' => $revisionNumber,
            'user_id'  => $userId,
        ]);

        return $targetLayout;
    }

    /**
     * Auto-cleanup expired revisions (scheduled command).
     */
    public function cleanupExpired(): int
    {
        return DB::table('growbuilder_page_revisions')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();
    }

    private function getNextRevisionNumber(int $siteId, int $pageId): int
    {
        $max = DB::table('growbuilder_page_revisions')
            ->where('site_id', $siteId)
            ->where('page_id', $pageId)
            ->max('revision_number');

        return (int)$max + 1;
    }

    private function pruneRevisions(int $siteId, int $pageId): void
    {
        $keepLimit = config('growbuilder.page_revisions.keep', self::DEFAULT_KEEP_REVISIONS);

        // Count total revisions excluding pre_upgrade (always keep those)
        $ids = DB::table('growbuilder_page_revisions')
            ->where('site_id', $siteId)
            ->where('page_id', $pageId)
            ->where('trigger', '!=', 'pre_upgrade')
            ->orderByDesc('revision_number')
            ->skip($keepLimit)
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            DB::table('growbuilder_page_revisions')->whereIn('id', $ids)->delete();
        }
    }
}
