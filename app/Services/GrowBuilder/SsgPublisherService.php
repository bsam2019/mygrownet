<?php

namespace App\Services\GrowBuilder;

use App\Domain\GrowBuilder\Contracts\SsgDeploymentEngineInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * SsgPublisherService — Static Site Generation (SSG) pipeline orchestrator.
 *
 * Compiles a GrowBuilder site's pages into static HTML/CSS asset bundles for
 * CDN-first serving. This is the #1 architectural priority from the spec (§32).
 *
 * Current driver: 'local' (stores in storage/app/ssg/)
 * Future driver:  'cloudflare' or 's3' via SsgDeploymentEngineInterface strategy
 *
 * Target: First Contentful Paint < 2s on 3G (§26)
 */
class SsgPublisherService
{
    public function __construct(
        private BusinessProfileService $profileService,
        private SeoSchemaService $seoService,
    ) {}

    /**
     * Build and deploy a site to static HTML.
     * Records result in growbuilder_ssg_deployments.
     *
     * @param  int    $siteId
     * @param  string $trigger 'publish', 'update', 'manual'
     * @return array  {success, cdn_url, pages_compiled, build_duration_ms, errors}
     */
    public function buildAndDeploy(int $siteId, string $trigger = 'publish'): array
    {
        $startTime = microtime(true);

        // Record deployment as pending
        $deploymentId = DB::table('growbuilder_ssg_deployments')->insertGetId([
            'site_id'    => $siteId,
            'status'     => 'building',
            'triggered_by' => $trigger,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $site    = DB::table('growbuilder_sites')->where('id', $siteId)->first();
            $pages   = DB::table('growbuilder_pages')->where('site_id', $siteId)->get();
            $profile = $this->profileService->getOrCreateForSite($siteId);

            $errors = [];
            $pagesCompiled = 0;
            $buildLog = [];

            $outputDir = "ssg/{$siteId}";
            Storage::disk('local')->makeDirectory($outputDir);

            foreach ($pages as $page) {
                try {
                    $html = $this->compilePage($site, $page, $profile);
                    $filename = ($page->slug === '/' || $page->slug === 'home') ? 'index.html' : ltrim($page->slug, '/') . '.html';
                    Storage::disk('local')->put("{$outputDir}/{$filename}", $html);
                    $pagesCompiled++;
                    $buildLog[] = "✅ {$filename}";
                } catch (\Throwable $e) {
                    $errors[] = "Page {$page->slug}: {$e->getMessage()}";
                    $buildLog[] = "❌ {$page->slug}: {$e->getMessage()}";
                }
            }

            // Generate SEO files
            $sitemap = $this->seoService->generateSitemap($siteId);
            Storage::disk('local')->put("{$outputDir}/sitemap.xml", $sitemap);

            $robotsTxt = $this->seoService->generateRobotsTxt($site);
            Storage::disk('local')->put("{$outputDir}/robots.txt", $robotsTxt);

            $buildLog[] = "✅ sitemap.xml";
            $buildLog[] = "✅ robots.txt";

            $durationMs = (int)((microtime(true) - $startTime) * 1000);
            $assetPath  = Storage::disk('local')->path($outputDir);

            // Update deployment record
            DB::table('growbuilder_ssg_deployments')->where('id', $deploymentId)->update([
                'status'             => empty($errors) ? 'deployed' : 'failed',
                'asset_zip_path'     => $assetPath,
                'cdn_url'            => url("/storage/ssg/{$siteId}/index.html"),
                'build_log'          => implode("\n", $buildLog),
                'build_duration_ms'  => $durationMs,
                'deployed_at'        => now(),
                'updated_at'         => now(),
            ]);

            // Update site's last SSG timestamp
            DB::table('growbuilder_sites')->where('id', $siteId)->update([
                'last_ssg_deployed_at' => now(),
                'ssg_enabled'          => true,
            ]);

            Log::info('GrowBuilder SSG: build complete', [
                'site_id' => $siteId,
                'pages'   => $pagesCompiled,
                'ms'      => $durationMs,
                'errors'  => count($errors),
            ]);

            return [
                'success'          => empty($errors),
                'cdn_url'          => url("/storage/ssg/{$siteId}/index.html"),
                'pages_compiled'   => $pagesCompiled,
                'build_duration_ms' => $durationMs,
                'errors'           => $errors,
            ];
        } catch (\Throwable $e) {
            $durationMs = (int)((microtime(true) - $startTime) * 1000);

            DB::table('growbuilder_ssg_deployments')->where('id', $deploymentId)->update([
                'status'    => 'failed',
                'build_log' => $e->getMessage(),
                'build_duration_ms' => $durationMs,
                'updated_at' => now(),
            ]);

            Log::error('GrowBuilder SSG: build failed', [
                'site_id' => $siteId,
                'error'   => $e->getMessage(),
            ]);

            return [
                'success'          => false,
                'cdn_url'          => null,
                'pages_compiled'   => 0,
                'build_duration_ms' => $durationMs,
                'errors'           => [$e->getMessage()],
            ];
        }
    }

    /**
     * Compile a single page into a self-contained HTML document.
     */
    private function compilePage(object $site, object $page, array $profile): string
    {
        $sections      = json_decode($page->sections ?? '[]', true);
        $pageTitle     = $page->title ?? ($site->name ?? 'Business Site');
        $businessName  = $profile['trade_name'] ?? $profile['legal_name'] ?? $pageTitle;
        $description   = $page->meta_description ?? ($profile['description'] ?? 'Welcome to our business');
        $canonicalUrl  = $site->custom_domain
            ? "https://{$site->custom_domain}" . ($page->slug !== '/' ? "/{$page->slug}" : '')
            : url("/sites/{$site->subdomain}/{$page->slug}");

        $jsonLd = (new SeoSchemaService())->buildJsonLdSchema((array)$site, $profile);
        $sectionsHtml = $this->renderSections($sections, $profile);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$pageTitle} — {$businessName}</title>
    <meta name="description" content="{$description}">
    <link rel="canonical" href="{$canonicalUrl}">
    <meta property="og:title" content="{$pageTitle} — {$businessName}">
    <meta property="og:description" content="{$description}">
    <meta property="og:url" content="{$canonicalUrl}">
    <meta name="robots" content="index, follow">
    <script type="application/ld+json">{$jsonLd}</script>
    <style>
        :root { --primary: #2563eb; --text: #1f2937; --bg: #ffffff; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; color: var(--text); background: var(--bg); line-height: 1.6; }
        .section { padding: 3rem 1rem; max-width: 1100px; margin: 0 auto; }
        img { max-width: 100%; height: auto; display: block; }
        a { color: var(--primary); }
        h1,h2,h3 { line-height: 1.2; }
        @media (max-width:640px) { .section { padding: 2rem 1rem; } }
    </style>
</head>
<body>
{$sectionsHtml}
</body>
</html>
HTML;
    }

    /**
     * Render page sections into static HTML strings.
     * This is a simplified renderer — the full section renderer is in StaticExportService.
     */
    private function renderSections(array $sections, array $profile): string
    {
        $html = '';
        foreach ($sections as $section) {
            $type    = $section['type'] ?? 'generic';
            $content = $section['content'] ?? [];
            $html   .= $this->renderSection($type, $content, $profile);
        }
        return $html;
    }

    private function renderSection(string $type, array $content, array $profile): string
    {
        $phone    = $profile['phone'] ?? '';
        $whatsapp = $profile['whatsapp'] ?? $phone;
        $waNumber = preg_replace('/[^0-9]/', '', $whatsapp);

        return match($type) {
            'hero' => sprintf(
                '<section class="section" style="background:linear-gradient(135deg,#1e40af,#3b82f6);color:#fff;max-width:100%%;padding:5rem 2rem;text-align:center">
                    <h1>%s</h1><p style="font-size:1.2rem;margin:1rem 0">%s</p>
                    %s
                 </section>',
                htmlspecialchars($content['heading'] ?? $profile['trade_name'] ?? ''),
                htmlspecialchars($content['subheading'] ?? $profile['tagline'] ?? ''),
                $waNumber ? "<a href=\"https://wa.me/{$waNumber}\" style=\"display:inline-block;background:#25d366;color:#fff;padding:.75rem 2rem;border-radius:8px;text-decoration:none;margin-top:1rem\">💬 WhatsApp Us</a>" : ''
            ),
            'about' => sprintf(
                '<section class="section"><h2>%s</h2><p>%s</p></section>',
                htmlspecialchars($content['heading'] ?? 'About Us'),
                htmlspecialchars($content['text'] ?? $profile['description'] ?? '')
            ),
            'services' => $this->renderServicesSection($content, $profile),
            'contact'  => $this->renderContactSection($content, $profile),
            default    => sprintf(
                '<section class="section"><div>%s</div></section>',
                htmlspecialchars($content['text'] ?? '')
            ),
        };
    }

    private function renderServicesSection(array $content, array $profile): string
    {
        $services = $content['services'] ?? ($profile['services_json'] ? json_decode($profile['services_json'], true) : []);
        $items    = '';
        foreach ($services as $service) {
            $name = htmlspecialchars($service['name'] ?? '');
            $desc = htmlspecialchars($service['description'] ?? '');
            $price = isset($service['price']) ? '<strong>K ' . htmlspecialchars($service['price']) . '</strong>' : '';
            $items .= "<div style=\"border:1px solid #e5e7eb;border-radius:8px;padding:1.5rem\"><h3>{$name}</h3><p>{$desc}</p>{$price}</div>";
        }
        return "<section class=\"section\"><h2>" . htmlspecialchars($content['heading'] ?? 'Our Services') . "</h2><div style=\"display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1rem;margin-top:1.5rem\">{$items}</div></section>";
    }

    private function renderContactSection(array $content, array $profile): string
    {
        $phone    = htmlspecialchars($profile['phone'] ?? '');
        $email    = htmlspecialchars($profile['email'] ?? '');
        $address  = htmlspecialchars($profile['physical_address'] ?? '');
        $whatsapp = preg_replace('/[^0-9]/', '', $profile['whatsapp'] ?? $profile['phone'] ?? '');

        $waLink = $whatsapp ? "<a href=\"https://wa.me/{$whatsapp}\" style=\"color:#25d366\">💬 WhatsApp</a>" : '';

        return "<section class=\"section\" style=\"background:#f9fafb;max-width:100%;padding:3rem 2rem\">
            <div style=\"max-width:800px;margin:0 auto\">
            <h2>" . htmlspecialchars($content['heading'] ?? 'Contact Us') . "</h2>
            <p>📍 {$address}</p><p>📞 {$phone}</p><p>✉️ {$email}</p><p>{$waLink}</p>
            </div></section>";
    }
}
