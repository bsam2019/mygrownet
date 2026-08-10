<?php

namespace App\Domain\GrowBuilder\Contracts;

/**
 * SsgDeploymentEngineInterface — Strategy Pattern contract for Static Site Generation (SSG).
 *
 * Compiles a GrowBuilder site's pages into static HTML/CSS/JS asset bundles and deploys them
 * to a storage backend (local disk, S3, Cloudflare R2, etc.).
 *
 * The active engine is resolved from config('growbuilder.ssg.driver') at runtime.
 */
interface SsgDeploymentEngineInterface
{
    /**
     * Compile a GrowBuilder site's pages into static HTML asset packages.
     *
     * @param  int   $siteId  The GrowBuilder site ID to compile
     * @return array          Compilation result: {success, asset_path, pages_compiled, build_duration_ms, errors[]}
     */
    public function compileSite(int $siteId): array;

    /**
     * Deploy compiled static assets to the configured CDN/storage backend.
     *
     * @param  int    $siteId    The GrowBuilder site ID
     * @param  string $assetPath Local path to compiled asset bundle (ZIP or directory)
     * @return array             Deploy result: {success, cdn_url, deployed_at, errors[]}
     */
    public function deploy(int $siteId, string $assetPath): array;

    /**
     * Purge the CDN cache for a specific site so the latest static build is served.
     *
     * @param  int    $siteId The GrowBuilder site ID
     * @param  string $cdnUrl The CDN URL to purge
     * @return bool           True if purge succeeded
     */
    public function purgeCache(int $siteId, string $cdnUrl): bool;

    /**
     * Return the human-readable name of this SSG deployment engine.
     */
    public function getEngineName(): string;
}
