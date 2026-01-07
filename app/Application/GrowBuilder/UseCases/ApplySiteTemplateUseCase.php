<?php

namespace App\Application\GrowBuilder\UseCases;

use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPage;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Models\GrowBuilder\SiteTemplate;
use Illuminate\Support\Facades\DB;

class ApplySiteTemplateUseCase
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository
    ) {}

    /**
     * Apply a site template to a newly created site
     */
    public function execute(int $siteId, int $templateId): void
    {
        $template = SiteTemplate::with('pages')->findOrFail($templateId);
        $site = GrowBuilderSite::findOrFail($siteId);

        DB::transaction(function () use ($site, $template) {
            // Apply theme settings
            if ($template->theme) {
                $site->theme = array_merge($site->theme ?? [], $template->theme);
            }

            // Apply site settings (navigation, footer, etc.)
            if ($template->settings) {
                $settings = $site->settings ?? [];
                
                // Merge navigation settings
                if (isset($template->settings['navigation'])) {
                    $settings['navigation'] = array_merge(
                        $settings['navigation'] ?? [],
                        $template->settings['navigation']
                    );
                    // Replace logo text with site name
                    $settings['navigation']['logoText'] = $site->name;
                }

                // Merge footer settings
                if (isset($template->settings['footer'])) {
                    $settings['footer'] = array_merge(
                        $settings['footer'] ?? [],
                        $template->settings['footer']
                    );
                    // Update copyright with site name
                    if (isset($settings['footer']['copyrightText'])) {
                        $settings['footer']['copyrightText'] = str_replace(
                            $template->name,
                            $site->name,
                            $settings['footer']['copyrightText']
                        );
                    }
                }

                $site->settings = $settings;
            }

            $site->save();

            // Delete existing pages (if any)
            GrowBuilderPage::where('site_id', $site->id)->delete();

            // Create pages from template and collect nav links
            $navLinks = [];
            foreach ($template->pages as $templatePage) {
                $content = $templatePage->content;
                
                // Replace template-specific text with site name where appropriate
                $content = $this->replaceTemplateReferences($content, $template->name, $site->name);

                GrowBuilderPage::create([
                    'site_id' => $site->id,
                    'title' => $templatePage->title,
                    'slug' => $templatePage->slug,
                    'is_homepage' => $templatePage->is_homepage,
                    'show_in_nav' => $templatePage->show_in_nav,
                    'content_json' => $content,
                    'nav_order' => $templatePage->sort_order,
                ]);

                // Add to nav links if it should show in nav
                if ($templatePage->show_in_nav) {
                    $navLinks[] = [
                        'id' => 'nav-' . $templatePage->slug,
                        'label' => $templatePage->title,
                        'url' => $templatePage->is_homepage ? '/' : '/' . $templatePage->slug,
                        'isExternal' => false,
                        'children' => [],
                    ];
                }
            }

            // Update navigation links in settings (use navItems for editor compatibility)
            $settings = $site->settings ?? [];
            $settings['navigation'] = $settings['navigation'] ?? [];
            $settings['navigation']['navItems'] = $navLinks;
            $site->settings = $settings;
            $site->save();

            // Increment template usage count
            $template->incrementUsage();
        });
    }

    /**
     * Replace template name references with site name in content
     */
    private function replaceTemplateReferences(array $content, string $templateName, string $siteName): array
    {
        $json = json_encode($content);
        
        // Replace template name variations
        $replacements = [
            $templateName => $siteName,
            strtolower($templateName) => strtolower($siteName),
            strtoupper($templateName) => strtoupper($siteName),
        ];

        foreach ($replacements as $search => $replace) {
            $json = str_replace($search, $replace, $json);
        }

        return json_decode($json, true);
    }
}
