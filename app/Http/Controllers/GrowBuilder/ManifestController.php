<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\Subdomain;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManifestController extends Controller
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository
    ) {}

    /**
     * Generate dynamic PWA manifest for GrowBuilder site
     */
    public function manifest(Request $request, string $subdomain)
    {
        try {
            $site = $this->siteRepository->findBySubdomain(Subdomain::fromString($subdomain));
        } catch (\Exception $e) {
            abort(404);
        }

        if (!$site) {
            abort(404);
        }

        $baseUrl = "https://{$subdomain}.mygrownet.com";
        $theme = $site->getTheme()?->toArray() ?? [];
        $themeColor = $theme['primaryColor'] ?? '#2563eb';
        $backgroundColor = $theme['backgroundColor'] ?? '#ffffff';
        
        // Use site logo or favicon for icons
        $icon = $site->getLogo() ?: $site->getFavicon();
        
        $manifest = [
            'name' => $site->getName(),
            'short_name' => $site->getName(),
            'description' => "Visit {$site->getName()}",
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => $backgroundColor,
            'theme_color' => $themeColor,
            'orientation' => 'portrait-primary',
            'icons' => [],
            'prefer_related_applications' => false,
            'categories' => ['business', 'lifestyle'],
        ];

        // Add icons if available
        if ($icon) {
            $manifest['icons'] = [
                [
                    'src' => $icon,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ],
                [
                    'src' => $icon,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ],
            ];
        }

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
