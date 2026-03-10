<?php

namespace App\Services\GrowBuilder;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use ZipArchive;

class StaticExportService
{
    private string $tempDir;
    private array $downloadedMedia = [];

    public function __construct(
        private TierRestrictionService $tierRestrictionService
    ) {}

    /**
     * Check if user can export site
     */
    public function canExport($user): bool
    {
        $restrictions = $this->tierRestrictionService->getRestrictions($user);
        return $restrictions['features']['static_export'] ?? false;
    }

    /**
     * Export site as static HTML
     */
    public function exportSite(GrowBuilderSite $site): string
    {
        // Use unique identifier to prevent conflicts between concurrent exports
        $uniqueId = uniqid('export-', true);
        $this->tempDir = storage_path('app/temp/' . $uniqueId . '-' . $site->id);
        
        try {
            // Create temp directory structure
            $this->createDirectoryStructure();
            
            // Generate HTML pages
            $this->generatePages($site);
            
            // Download and copy media files
            $this->copyMediaFiles($site);
            
            // Generate CSS
            $this->generateCSS($site);
            
            // Generate JavaScript
            $this->generateJS($site);
            
            // Create README
            $this->createReadme($site);
            
            // Create ZIP file
            $zipPath = $this->createZip($site);
            
            // Cleanup temp directory
            $this->cleanup();
            
            return $zipPath;
            
        } catch (\Exception $e) {
            // Ensure cleanup happens even on failure
            $this->cleanup();
            throw $e;
        }
    }

    /**
     * Create directory structure
     */
    private function createDirectoryStructure(): void
    {
        mkdir($this->tempDir, 0755, true);
        mkdir($this->tempDir . '/css', 0755, true);
        mkdir($this->tempDir . '/js', 0755, true);
        mkdir($this->tempDir . '/images', 0755, true);
        mkdir($this->tempDir . '/media', 0755, true);
    }

    /**
     * Generate HTML pages
     */
    private function generatePages(GrowBuilderSite $site): void
    {
        $pages = $site->pages()->orderBy('nav_order')->get();
        
        foreach ($pages as $page) {
            $html = $this->generatePageHTML($site, $page, $pages);
            $filename = $page->is_homepage ? 'index.html' : $page->slug . '.html';
            file_put_contents($this->tempDir . '/' . $filename, $html);
        }
    }

    /**
     * Generate HTML for a single page
     */
    private function generatePageHTML(GrowBuilderSite $site, GrowBuilderPage $page, $allPages): string
    {
        $theme = $site->theme ?? [];
        $navigation = $site->settings['navigation'] ?? [];
        $footer = $site->settings['footer'] ?? [];
        
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($page->title . ' - ' . $site->name) . '</title>
    <meta name="description" content="' . htmlspecialchars($site->description ?? '') . '">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="' . ($site->favicon ? 'images/favicon.ico' : '') . '">
</head>
<body>
    ' . $this->generateNavigation($site, $navigation, $allPages, $page) . '
    
    <main>
        ' . $this->generateSections($page->content_json['sections'] ?? [], $theme) . '
    </main>
    
    ' . $this->generateFooter($site, $footer) . '
    
    <script src="js/main.js"></script>
</body>
</html>';
        
        return $html;
    }

    /**
     * Generate navigation HTML
     */
    private function generateNavigation(GrowBuilderSite $site, array $navigation, $pages, $currentPage): string
    {
        $logoText = $navigation['logoText'] ?? $site->name;
        $navItems = $navigation['navItems'] ?? [];
        $sticky = $navigation['sticky'] ?? true;
        
        $stickyClass = $sticky ? 'sticky top-0 z-50' : '';
        
        $html = '<nav class="' . $stickyClass . ' bg-white shadow-md">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center">
                <a href="index.html" class="text-xl font-bold text-gray-900">' . htmlspecialchars($logoText) . '</a>
            </div>
            <div class="hidden md:flex space-x-8">';
        
        foreach ($navItems as $item) {
            $url = $item['url'] === '/' ? 'index.html' : ltrim($item['url'], '/') . '.html';
            $active = $currentPage->slug === ltrim($item['url'], '/') || ($item['url'] === '/' && $currentPage->is_homepage);
            $activeClass = $active ? 'text-blue-600 font-semibold' : 'text-gray-700 hover:text-blue-600';
            
            $html .= '<a href="' . $url . '" class="' . $activeClass . '">' . htmlspecialchars($item['label']) . '</a>';
        }
        
        $html .= '</div>
            <button class="md:hidden" id="mobile-menu-button">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
        <div class="hidden md:hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1">';
        
        foreach ($navItems as $item) {
            $url = $item['url'] === '/' ? 'index.html' : ltrim($item['url'], '/') . '.html';
            $html .= '<a href="' . $url . '" class="block px-3 py-2 text-gray-700 hover:bg-gray-100">' . htmlspecialchars($item['label']) . '</a>';
        }
        
        $html .= '</div>
        </div>
    </div>
</nav>';
        
        return $html;
    }

    /**
     * Generate sections HTML
     */
    private function generateSections(array $sections, array $theme): string
    {
        $html = '';
        
        foreach ($sections as $section) {
            $html .= $this->generateSection($section, $theme);
        }
        
        return $html;
    }

    /**
     * Generate single section HTML
     */
    private function generateSection(array $section, array $theme): string
    {
        $type = $section['type'] ?? 'text';
        $content = $section['content'] ?? [];
        $style = $section['style'] ?? [];
        
        $bgColor = $style['backgroundColor'] ?? '#ffffff';
        $textColor = $style['textColor'] ?? '#111827';
        $minHeight = $style['minHeight'] ?? null;
        
        $styleAttr = "background-color: {$bgColor}; color: {$textColor};";
        if ($minHeight) {
            $styleAttr .= " min-height: {$minHeight}px;";
        }
        
        return match($type) {
            'hero' => $this->generateHeroSection($content, $styleAttr),
            'page-header' => $this->generatePageHeaderSection($content, $styleAttr),
            'stats' => $this->generateStatsSection($content, $styleAttr),
            'about' => $this->generateAboutSection($content, $styleAttr),
            'services' => $this->generateServicesSection($content, $styleAttr),
            'features' => $this->generateFeaturesSection($content, $styleAttr),
            'contact' => $this->generateContactSection($content, $styleAttr),
            'cta' => $this->generateCTASection($content, $styleAttr),
            'cta-banner' => $this->generateCTABannerSection($content, $styleAttr),
            'testimonials' => $this->generateTestimonialsSection($content, $styleAttr),
            'gallery' => $this->generateGallerySection($content, $styleAttr),
            'text' => $this->generateTextSection($content, $styleAttr),
            'faq' => $this->generateFAQSection($content, $styleAttr),
            'pricing' => $this->generatePricingSection($content, $styleAttr),
            'products' => $this->generateProductsSection($content, $styleAttr),
            'team' => $this->generateTeamSection($content, $styleAttr),
            'timeline' => $this->generateTimelineSection($content, $styleAttr),
            'video-hero' => $this->generateVideoHeroSection($content, $styleAttr),
            'logo-cloud' => $this->generateLogoCloudSection($content, $styleAttr),
            'map' => $this->generateMapSection($content, $styleAttr),
            'video' => $this->generateVideoSection($content, $styleAttr),
            'blog' => $this->generateBlogSection($content, $styleAttr),
            default => ''
        };
    }

    private function generateStatsSection(array $content, string $style): string
    {
        $layout = $content['layout'] ?? 'default';
        $title = htmlspecialchars($content['title'] ?? '');
        $subtitle = htmlspecialchars($content['subtitle'] ?? '');
        $items = $content['items'] ?? [];
        
        // Horizontal layout
        if ($layout === 'horizontal' || $layout === 'row') {
            $html = '<section class="stats-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        ' . ($title ? '<div class="text-center mb-12">
            <h2 class="text-3xl font-bold mb-3">' . $title . '</h2>
            ' . ($subtitle ? '<p class="opacity-90">' . $subtitle . '</p>' : '') . '
        </div>' : '') . '
        <div class="flex flex-wrap justify-center gap-12 text-center">';
            
            foreach ($items as $stat) {
                $value = htmlspecialchars($stat['value'] ?? '');
                $label = htmlspecialchars($stat['label'] ?? '');
                
                $html .= '<div>
                    <div class="text-4xl font-bold mb-2">' . $value . '</div>
                    <div class="text-sm opacity-90">' . $label . '</div>
                </div>';
            }
            
            $html .= '</div>
    </div>
</section>';
            return $html;
        }
        
        // Icons layout
        if ($layout === 'icons') {
            $html = '<section class="stats-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        ' . ($title ? '<div class="text-center mb-12">
            <h2 class="text-3xl font-bold mb-3">' . $title . '</h2>
            ' . ($subtitle ? '<p class="opacity-90">' . $subtitle . '</p>' : '') . '
        </div>' : '') . '
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">';
            
            foreach ($items as $stat) {
                $value = htmlspecialchars($stat['value'] ?? '');
                $label = htmlspecialchars($stat['label'] ?? '');
                $icon = htmlspecialchars($stat['icon'] ?? '📊');
                
                $html .= '<div class="text-center">
                    <div class="text-4xl mb-3">' . $icon . '</div>
                    <div class="text-3xl font-bold mb-2">' . $value . '</div>
                    <div class="text-sm opacity-90">' . $label . '</div>
                </div>';
            }
            
            $html .= '</div>
    </div>
</section>';
            return $html;
        }
        
        // Default grid layout
        $html = '<section class="stats-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        ' . ($title ? '<div class="text-center mb-12">
            <h2 class="text-3xl font-bold mb-3">' . $title . '</h2>
            ' . ($subtitle ? '<p class="opacity-90">' . $subtitle . '</p>' : '') . '
        </div>' : '') . '
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">';
        
        foreach ($items as $stat) {
            $value = htmlspecialchars($stat['value'] ?? '');
            $label = htmlspecialchars($stat['label'] ?? '');
            
            $html .= '<div>
                <div class="text-4xl font-bold mb-2">' . $value . '</div>
                <div class="text-sm opacity-90">' . $label . '</div>
            </div>';
        }
        
        $html .= '</div>
    </div>
</section>';
        
        return $html;
    }

    private function generatePageHeaderSection(array $content, string $style): string
    {
        $title = htmlspecialchars($content['title'] ?? '');
        $subtitle = htmlspecialchars($content['subtitle'] ?? '');
        $bgImage = $this->processMediaUrl($content['backgroundImage'] ?? '');
        
        $bgStyle = $bgImage ? "background-image: url('{$bgImage}'); background-size: cover; background-position: center;" : '';
        
        return '<section class="page-header-section py-16 text-center" style="' . $style . ' ' . $bgStyle . '">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold mb-4">' . $title . '</h1>
        ' . ($subtitle ? '<p class="text-xl">' . $subtitle . '</p>' : '') . '
    </div>
</section>';
    }

    private function generateHeroSection(array $content, string $style): string
    {
        $layout = $content['layout'] ?? 'default';
        
        // Handle slideshow/slider layout
        if ($layout === 'slideshow' || $layout === 'slider') {
            return $this->generateHeroSlideshow($content, $style);
        }
        
        // Split-left layout
        if ($layout === 'split-left') {
            $title = htmlspecialchars($content['title'] ?? '');
            $subtitle = htmlspecialchars($content['subtitle'] ?? '');
            $buttonText = htmlspecialchars($content['buttonText'] ?? '');
            $buttonLink = htmlspecialchars($content['buttonLink'] ?? '#');
            $image = $this->processMediaUrl($content['image'] ?? $content['backgroundImage'] ?? '');
            
            return '<section class="hero-section py-20" style="' . $style . '">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-8 items-center">
            <div>
                <h1 class="text-5xl font-bold mb-4">' . $title . '</h1>
                <p class="text-xl mb-8">' . $subtitle . '</p>
                ' . ($buttonText ? '<a href="' . $buttonLink . '" class="btn-primary">' . $buttonText . '</a>' : '') . '
            </div>
            ' . ($image ? '<div><img src="' . $image . '" alt="' . $title . '" class="rounded-lg shadow-lg w-full"></div>' : '') . '
        </div>
    </div>
</section>';
        }
        
        // Split-right layout
        if ($layout === 'split-right') {
            $title = htmlspecialchars($content['title'] ?? '');
            $subtitle = htmlspecialchars($content['subtitle'] ?? '');
            $buttonText = htmlspecialchars($content['buttonText'] ?? '');
            $buttonLink = htmlspecialchars($content['buttonLink'] ?? '#');
            $image = $this->processMediaUrl($content['image'] ?? $content['backgroundImage'] ?? '');
            
            return '<section class="hero-section py-20" style="' . $style . '">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-8 items-center">
            ' . ($image ? '<div><img src="' . $image . '" alt="' . $title . '" class="rounded-lg shadow-lg w-full"></div>' : '') . '
            <div>
                <h1 class="text-5xl font-bold mb-4">' . $title . '</h1>
                <p class="text-xl mb-8">' . $subtitle . '</p>
                ' . ($buttonText ? '<a href="' . $buttonLink . '" class="btn-primary">' . $buttonText . '</a>' : '') . '
            </div>
        </div>
    </div>
</section>';
        }
        
        // Default hero layout
        $title = htmlspecialchars($content['title'] ?? '');
        $subtitle = htmlspecialchars($content['subtitle'] ?? '');
        $buttonText = htmlspecialchars($content['buttonText'] ?? '');
        $buttonLink = htmlspecialchars($content['buttonLink'] ?? '#');
        $bgImage = $this->processMediaUrl($content['backgroundImage'] ?? '');
        
        $bgStyle = $bgImage ? "background-image: url('{$bgImage}'); background-size: cover; background-position: center;" : '';
        
        return '<section class="hero-section py-20" style="' . $style . ' ' . $bgStyle . '">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-4">' . $title . '</h1>
        <p class="text-xl mb-8">' . $subtitle . '</p>
        ' . ($buttonText ? '<a href="' . $buttonLink . '" class="btn-primary">' . $buttonText . '</a>' : '') . '
    </div>
</section>';
    }

    private function generateHeroSlideshow(array $content, string $style): string
    {
        $slides = $content['slides'] ?? [];
        
        if (empty($slides)) {
            // Fallback to single slide
            return $this->generateHeroSection($content, $style);
        }
        
        $html = '<section class="hero-slideshow" style="' . $style . '">
    <div class="slideshow-container">';
        
        foreach ($slides as $index => $slide) {
            $title = htmlspecialchars($slide['title'] ?? '');
            $subtitle = htmlspecialchars($slide['subtitle'] ?? '');
            $buttonText = htmlspecialchars($slide['buttonText'] ?? '');
            $buttonLink = htmlspecialchars($slide['buttonLink'] ?? '#');
            $bgImage = $this->processMediaUrl($slide['image'] ?? $slide['backgroundImage'] ?? '');
            
            $bgStyle = $bgImage ? "background-image: url('{$bgImage}'); background-size: cover; background-position: center;" : '';
            $activeClass = $index === 0 ? 'active' : '';
            
            $html .= '<div class="slide ' . $activeClass . '" style="' . $bgStyle . '">
        <div class="container mx-auto px-4 text-center py-20">
            <h1 class="text-5xl font-bold mb-4 text-white">' . $title . '</h1>
            <p class="text-xl mb-8 text-white">' . $subtitle . '</p>
            ' . ($buttonText ? '<a href="' . $buttonLink . '" class="btn-primary">' . $buttonText . '</a>' : '') . '
        </div>
    </div>';
        }
        
        // Add navigation dots
        $html .= '<div class="slideshow-dots">';
        foreach ($slides as $index => $slide) {
            $activeClass = $index === 0 ? 'active' : '';
            $html .= '<span class="dot ' . $activeClass . '" onclick="currentSlide(' . ($index + 1) . ')"></span>';
        }
        $html .= '</div>
    </div>
</section>';
        
        return $html;
    }

    private function generateAboutSection(array $content, string $style): string
    {
        $layout = $content['layout'] ?? 'default';
        $title = htmlspecialchars($content['title'] ?? '');
        $description = $content['description'] ?? '';
        $image = $this->processMediaUrl($content['image'] ?? '');
        
        // Handle image-right layout
        if ($layout === 'image-right') {
            return '<section class="about-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-8 items-center">
            <div>
                <h2 class="text-3xl font-bold mb-4">' . $title . '</h2>
                <div class="text-lg">' . nl2br(htmlspecialchars($description)) . '</div>
            </div>
            ' . ($image ? '<div><img src="' . $image . '" alt="' . $title . '" class="rounded-lg shadow-lg w-full"></div>' : '') . '
        </div>
    </div>
</section>';
        }
        
        // Handle image-left layout (default)
        return '<section class="about-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-8 items-center">
            ' . ($image ? '<div><img src="' . $image . '" alt="' . $title . '" class="rounded-lg shadow-lg w-full"></div>' : '') . '
            <div>
                <h2 class="text-3xl font-bold mb-4">' . $title . '</h2>
                <div class="text-lg">' . nl2br(htmlspecialchars($description)) . '</div>
            </div>
        </div>
    </div>
</section>';
    }

    private function generateServicesSection(array $content, string $style): string
    {
        $layout = $content['layout'] ?? 'grid';
        $title = htmlspecialchars($content['title'] ?? '');
        $subtitle = htmlspecialchars($content['subtitle'] ?? '');
        $items = $content['items'] ?? [];
        $columns = $content['columns'] ?? 3;
        
        // Alternating layout
        if ($layout === 'alternating') {
            $html = '<section class="services-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-4">' . $title . '</h2>
        ' . ($subtitle ? '<p class="text-center mb-12">' . $subtitle . '</p>' : '') . '
        <div class="max-w-5xl mx-auto space-y-12">';
            
            foreach ($items as $index => $item) {
                $itemTitle = htmlspecialchars($item['title'] ?? '');
                $itemDesc = htmlspecialchars($item['description'] ?? '');
                $itemImage = $this->processMediaUrl($item['image'] ?? '');
                $isEven = $index % 2 === 0;
                
                $html .= '<div class="grid md:grid-cols-2 gap-8 items-center">';
                
                if ($isEven) {
                    // Image left, text right
                    if ($itemImage) {
                        $html .= '<div><img src="' . $itemImage . '" alt="' . $itemTitle . '" class="rounded-lg shadow-md w-full"></div>';
                    }
                    $html .= '<div>
                        <h3 class="text-2xl font-semibold mb-3">' . $itemTitle . '</h3>
                        <p class="text-gray-600">' . $itemDesc . '</p>
                    </div>';
                } else {
                    // Text left, image right
                    $html .= '<div>
                        <h3 class="text-2xl font-semibold mb-3">' . $itemTitle . '</h3>
                        <p class="text-gray-600">' . $itemDesc . '</p>
                    </div>';
                    if ($itemImage) {
                        $html .= '<div><img src="' . $itemImage . '" alt="' . $itemTitle . '" class="rounded-lg shadow-md w-full"></div>';
                    }
                }
                
                $html .= '</div>';
            }
            
            $html .= '</div>
    </div>
</section>';
            return $html;
        }
        
        // Cards layout (same as cards-images)
        if ($layout === 'cards' || $layout === 'cards-images') {
            $html = '<section class="services-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-4">' . $title . '</h2>
        ' . ($subtitle ? '<p class="text-center mb-12">' . $subtitle . '</p>' : '') . '
        <div class="grid md:grid-cols-' . $columns . ' gap-6">';
            
            foreach ($items as $item) {
                $itemTitle = htmlspecialchars($item['title'] ?? '');
                $itemDesc = htmlspecialchars($item['description'] ?? '');
                $itemImage = $this->processMediaUrl($item['image'] ?? '');
                
                $html .= '<div class="service-card bg-white rounded-lg shadow-md overflow-hidden">';
                if ($itemImage) {
                    $html .= '<img src="' . $itemImage . '" alt="' . $itemTitle . '" class="w-full h-48 object-cover">';
                }
                $html .= '<div class="p-5">
                    <h3 class="text-xl font-semibold mb-2">' . $itemTitle . '</h3>
                    <p>' . $itemDesc . '</p>
                </div>
            </div>';
            }
            
            $html .= '</div>
    </div>
</section>';
            return $html;
        }
        
        // List layout
        if ($layout === 'list') {
            $html = '<section class="services-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">' . $title . '</h2>
        <div class="space-y-4 max-w-3xl mx-auto">';
            
            foreach ($items as $item) {
                $itemTitle = htmlspecialchars($item['title'] ?? '');
                $itemDesc = htmlspecialchars($item['description'] ?? '');
                
                $html .= '<div class="flex gap-4 items-start p-4 bg-white rounded-lg shadow-sm">
                    <div class="flex-1">
                        <h3 class="text-xl font-semibold mb-2">' . $itemTitle . '</h3>
                        <p>' . $itemDesc . '</p>
                    </div>
                </div>';
            }
            
            $html .= '</div>
    </div>
</section>';
            return $html;
        }
        
        // Default grid layout
        $html = '<section class="services-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-4">' . $title . '</h2>
        ' . ($subtitle ? '<p class="text-center mb-12">' . $subtitle . '</p>' : '') . '
        <div class="grid md:grid-cols-' . $columns . ' gap-8">';
        
        foreach ($items as $item) {
            $itemTitle = htmlspecialchars($item['title'] ?? '');
            $itemDesc = htmlspecialchars($item['description'] ?? '');
            
            $html .= '<div class="service-card p-6 bg-white rounded-lg shadow-md">
                <h3 class="text-xl font-semibold mb-3">' . $itemTitle . '</h3>
                <p>' . $itemDesc . '</p>
            </div>';
        }
        
        $html .= '</div>
    </div>
</section>';
        
        return $html;
    }

    private function generateFeaturesSection(array $content, string $style): string
    {
        $layout = $content['layout'] ?? 'grid';
        $title = htmlspecialchars($content['title'] ?? '');
        $subtitle = htmlspecialchars($content['subtitle'] ?? '');
        $items = $content['items'] ?? [];
        $columns = $content['columns'] ?? 3;
        
        // Checklist layout
        if ($layout === 'checklist') {
            $html = '<section class="features-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-4">' . $title . '</h2>
        ' . ($subtitle ? '<p class="text-center mb-12">' . $subtitle . '</p>' : '') . '
        <div class="max-w-3xl mx-auto space-y-4">';
            
            foreach ($items as $item) {
                $itemTitle = htmlspecialchars($item['title'] ?? '');
                $itemDesc = htmlspecialchars($item['description'] ?? '');
                
                $html .= '<div class="flex items-start gap-3 p-4 bg-white rounded-lg border border-gray-100">
                    <span class="text-green-500 text-xl flex-shrink-0">✓</span>
                    <div>
                        <h3 class="font-semibold mb-1">' . $itemTitle . '</h3>
                        <p class="text-gray-600 text-sm">' . $itemDesc . '</p>
                    </div>
                </div>';
            }
            
            $html .= '</div>
    </div>
</section>';
            return $html;
        }
        
        // Steps layout
        if ($layout === 'steps') {
            $html = '<section class="features-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-4">' . $title . '</h2>
        ' . ($subtitle ? '<p class="text-center mb-12">' . $subtitle . '</p>' : '') . '
        <div class="max-w-4xl mx-auto space-y-8">';
            
            foreach ($items as $index => $item) {
                $itemTitle = htmlspecialchars($item['title'] ?? '');
                $itemDesc = htmlspecialchars($item['description'] ?? '');
                
                $html .= '<div class="flex gap-6 items-start">
                    <div class="flex-shrink-0 w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                        ' . ($index + 1) . '
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-semibold mb-2">' . $itemTitle . '</h3>
                        <p class="text-gray-600">' . $itemDesc . '</p>
                    </div>
                </div>';
            }
            
            $html .= '</div>
    </div>
</section>';
            return $html;
        }
        
        // Default grid layout
        return $this->generateServicesSection($content, $style);
    }

    private function generateContactSection(array $content, string $style): string
    {
        $layout = $content['layout'] ?? 'default';
        $title = htmlspecialchars($content['title'] ?? '');
        $description = htmlspecialchars($content['description'] ?? '');
        $email = htmlspecialchars($content['email'] ?? '');
        $phone = htmlspecialchars($content['phone'] ?? '');
        $address = htmlspecialchars($content['address'] ?? '');
        $showForm = $content['showForm'] ?? true;
        
        // Side-by-side layout
        if ($layout === 'side-by-side') {
            return '<section class="contact-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-4">' . $title . '</h2>
        <p class="text-center mb-12">' . $description . '</p>
        <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
            <div>
                <h3 class="font-semibold text-xl mb-4">Get in Touch</h3>
                <div class="space-y-4">
                    ' . ($email ? '<p><strong>Email:</strong> <a href="mailto:' . $email . '" class="text-blue-600">' . $email . '</a></p>' : '') . '
                    ' . ($phone ? '<p><strong>Phone:</strong> <a href="tel:' . $phone . '" class="text-blue-600">' . $phone . '</a></p>' : '') . '
                    ' . ($address ? '<p><strong>Address:</strong> ' . nl2br($address) . '</p>' : '') . '
                </div>
            </div>
            ' . ($showForm ? '<div>
                <form class="space-y-4">
                    <input type="text" placeholder="Your Name" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                    <input type="email" placeholder="Your Email" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                    <textarea placeholder="Your Message" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg"></textarea>
                    <button type="submit" class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                        Send Message
                    </button>
                </form>
            </div>' : '') . '
        </div>
    </div>
</section>';
        }
        
        // With map layout
        if ($layout === 'with-map') {
            $embedUrl = htmlspecialchars($content['embedUrl'] ?? '');
            return '<section class="contact-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-4">' . $title . '</h2>
        <p class="text-center mb-12">' . $description . '</p>
        <div class="max-w-4xl mx-auto">
            <div class="grid md:grid-cols-3 gap-4 mb-8">
                ' . ($email ? '<div class="text-center p-4 bg-white rounded-lg">
                    <span class="text-2xl mb-2 block">✉️</span>
                    <p class="text-sm text-gray-600">' . $email . '</p>
                </div>' : '') . '
                ' . ($phone ? '<div class="text-center p-4 bg-white rounded-lg">
                    <span class="text-2xl mb-2 block">📞</span>
                    <p class="text-sm text-gray-600">' . $phone . '</p>
                </div>' : '') . '
                ' . ($address ? '<div class="text-center p-4 bg-white rounded-lg">
                    <span class="text-2xl mb-2 block">📍</span>
                    <p class="text-sm text-gray-600">' . $address . '</p>
                </div>' : '') . '
            </div>
            ' . ($embedUrl ? '<div class="aspect-video rounded-lg overflow-hidden bg-gray-100">
                <iframe src="' . $embedUrl . '" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
            </div>' : '') . '
        </div>
    </div>
</section>';
        }
        
        // Default layout
        return '<section class="contact-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-4">' . $title . '</h2>
        <p class="text-center mb-12">' . $description . '</p>
        <div class="max-w-2xl mx-auto">
            <div class="grid md:grid-cols-3 gap-4 mb-8">
                ' . ($email ? '<div class="text-center p-4 bg-white rounded-lg">
                    <span class="text-2xl mb-2 block">✉️</span>
                    <p class="text-sm text-gray-600">' . $email . '</p>
                </div>' : '') . '
                ' . ($phone ? '<div class="text-center p-4 bg-white rounded-lg">
                    <span class="text-2xl mb-2 block">📞</span>
                    <p class="text-sm text-gray-600">' . $phone . '</p>
                </div>' : '') . '
                ' . ($address ? '<div class="text-center p-4 bg-white rounded-lg">
                    <span class="text-2xl mb-2 block">📍</span>
                    <p class="text-sm text-gray-600">' . $address . '</p>
                </div>' : '') . '
            </div>
            ' . ($showForm ? '<form class="space-y-4 bg-white p-6 rounded-lg shadow-sm">
                <input type="text" placeholder="Your Name" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                <input type="email" placeholder="Your Email" class="w-full px-4 py-3 border border-gray-300 rounded-lg" />
                <textarea placeholder="Your Message" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg"></textarea>
                <button type="submit" class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    Send Message
                </button>
            </form>' : '') . '
        </div>
    </div>
</section>';
    }

    private function generateCTASection(array $content, string $style): string
    {
        $layout = $content['layout'] ?? 'default';
        $title = htmlspecialchars($content['title'] ?? '');
        $description = htmlspecialchars($content['description'] ?? '');
        $buttonText = htmlspecialchars($content['buttonText'] ?? '');
        $buttonLink = htmlspecialchars($content['buttonLink'] ?? '#');
        
        // Centered layout
        if ($layout === 'centered') {
            return '<section class="cta-section py-16 text-center" style="' . $style . '">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto">
            <h2 class="text-3xl font-bold mb-4">' . $title . '</h2>
            <p class="text-xl mb-8">' . $description . '</p>
            ' . ($buttonText ? '<a href="' . $buttonLink . '" class="btn-primary">' . $buttonText . '</a>' : '') . '
        </div>
    </div>
</section>';
        }
        
        // Split layout
        if ($layout === 'split') {
            return '<section class="cta-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-8 items-center max-w-5xl mx-auto">
            <div>
                <h2 class="text-3xl font-bold mb-4">' . $title . '</h2>
                <p class="text-xl mb-6">' . $description . '</p>
            </div>
            <div class="text-right">
                ' . ($buttonText ? '<a href="' . $buttonLink . '" class="btn-primary">' . $buttonText . '</a>' : '') . '
            </div>
        </div>
    </div>
</section>';
        }
        
        // With image layout
        if ($layout === 'with-image') {
            $image = $this->processMediaUrl($content['image'] ?? '');
            return '<section class="cta-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-8 items-center max-w-5xl mx-auto">
            ' . ($image ? '<div><img src="' . $image . '" alt="' . $title . '" class="rounded-lg shadow-lg w-full"></div>' : '') . '
            <div>
                <h2 class="text-3xl font-bold mb-4">' . $title . '</h2>
                <p class="text-xl mb-8">' . $description . '</p>
                ' . ($buttonText ? '<a href="' . $buttonLink . '" class="btn-primary">' . $buttonText . '</a>' : '') . '
            </div>
        </div>
    </div>
</section>';
        }
        
        // Default layout
        return '<section class="cta-section py-16 text-center" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-4">' . $title . '</h2>
        <p class="text-xl mb-8">' . $description . '</p>
        ' . ($buttonText ? '<a href="' . $buttonLink . '" class="btn-primary">' . $buttonText . '</a>' : '') . '
    </div>
</section>';
    }

    private function generateTestimonialsSection(array $content, string $style): string
    {
        $layout = $content['layout'] ?? 'grid';
        $title = htmlspecialchars($content['title'] ?? '');
        $items = $content['items'] ?? [];
        
        // Single layout
        if ($layout === 'single' && !empty($items)) {
            $item = $items[0];
            $name = htmlspecialchars($item['name'] ?? '');
            $text = htmlspecialchars($item['text'] ?? '');
            $role = htmlspecialchars($item['role'] ?? '');
            
            return '<section class="testimonials-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        ' . ($title ? '<h2 class="text-3xl font-bold text-center mb-12">' . $title . '</h2>' : '') . '
        <div class="max-w-3xl mx-auto text-center">
            <div class="testimonial-card p-8 bg-white rounded-lg shadow-md">
                <p class="text-xl mb-6 italic">"' . $text . '"</p>
                <p class="font-semibold text-lg">' . $name . '</p>
                <p class="text-gray-600">' . $role . '</p>
            </div>
        </div>
    </div>
</section>';
        }
        
        // Carousel layout (shows first 3)
        if ($layout === 'carousel') {
            $html = '<section class="testimonials-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        ' . ($title ? '<h2 class="text-3xl font-bold text-center mb-12">' . $title . '</h2>' : '') . '
        <div class="max-w-4xl mx-auto">
            <div class="grid md:grid-cols-1 gap-8">';
            
            // Show first testimonial prominently
            foreach (array_slice($items, 0, 1) as $item) {
                $name = htmlspecialchars($item['name'] ?? '');
                $text = htmlspecialchars($item['text'] ?? '');
                $role = htmlspecialchars($item['role'] ?? '');
                
                $html .= '<div class="testimonial-card p-8 bg-white rounded-lg shadow-md text-center">
                    <p class="text-xl mb-6 italic">"' . $text . '"</p>
                    <p class="font-semibold text-lg">' . $name . '</p>
                    <p class="text-gray-600">' . $role . '</p>
                </div>';
            }
            
            $html .= '</div>
        </div>
    </div>
</section>';
            return $html;
        }
        
        // Default grid layout
        $html = '<section class="testimonials-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        ' . ($title ? '<h2 class="text-3xl font-bold text-center mb-12">' . $title . '</h2>' : '') . '
        <div class="grid md:grid-cols-2 gap-8">';
        
        foreach ($items as $item) {
            $name = htmlspecialchars($item['name'] ?? '');
            $text = htmlspecialchars($item['text'] ?? '');
            $role = htmlspecialchars($item['role'] ?? '');
            
            $html .= '<div class="testimonial-card p-6 bg-white rounded-lg shadow-md">
                <p class="mb-4 italic">"' . $text . '"</p>
                <p class="font-semibold">' . $name . '</p>
                <p class="text-sm text-gray-600">' . $role . '</p>
            </div>';
        }
        
        $html .= '</div>
    </div>
</section>';
        
        return $html;
    }

    private function generateGallerySection(array $content, string $style): string
    {
        $title = htmlspecialchars($content['title'] ?? '');
        $images = $content['images'] ?? [];
        
        $html = '<section class="gallery-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">' . $title . '</h2>
        <div class="grid md:grid-cols-3 gap-4">';
        
        foreach ($images as $image) {
            $url = $this->processMediaUrl($image['url'] ?? '');
            $alt = htmlspecialchars($image['alt'] ?? '');
            
            if ($url) {
                $html .= '<img src="' . $url . '" alt="' . $alt . '" class="rounded-lg shadow-md w-full h-64 object-cover">';
            }
        }
        
        $html .= '</div>
    </div>
</section>';
        
        return $html;
    }

    private function generateTextSection(array $content, string $style): string
    {
        $text = $content['content'] ?? '';
        
        return '<section class="text-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <div class="prose max-w-4xl mx-auto">' . $text . '</div>
    </div>
</section>';
    }

    private function generateFAQSection(array $content, string $style): string
    {
        $layout = $content['layout'] ?? 'accordion';
        $title = htmlspecialchars($content['title'] ?? 'Frequently Asked Questions');
        $items = $content['items'] ?? [];
        
        // Two Column Layout
        if ($layout === 'two-column') {
            $html = '<section class="faq-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">' . $title . '</h2>
        <div class="grid md:grid-cols-2 gap-6">';
            
            foreach ($items as $item) {
                $question = htmlspecialchars($item['question'] ?? '');
                $answer = htmlspecialchars($item['answer'] ?? '');
                
                $html .= '<div class="bg-white p-6 rounded-lg border border-gray-100">
                    <h3 class="font-semibold text-gray-900 mb-3">' . $question . '</h3>
                    <p class="text-gray-600">' . $answer . '</p>
                </div>';
            }
            
            $html .= '</div>
    </div>
</section>';
            return $html;
        }
        
        // List Layout
        if ($layout === 'list') {
            $html = '<section class="faq-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">' . $title . '</h2>
        <div class="max-w-3xl mx-auto space-y-6">';
            
            foreach ($items as $item) {
                $question = htmlspecialchars($item['question'] ?? '');
                $answer = htmlspecialchars($item['answer'] ?? '');
                
                $html .= '<div class="border-b border-gray-200 pb-6">
                    <h3 class="font-semibold text-gray-900 mb-2 text-lg">' . $question . '</h3>
                    <p class="text-gray-600">' . $answer . '</p>
                </div>';
            }
            
            $html .= '</div>
    </div>
</section>';
            return $html;
        }
        
        // Default Accordion Layout
        $html = '<section class="faq-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">' . $title . '</h2>
        <div class="max-w-3xl mx-auto space-y-4">';
        
        foreach ($items as $item) {
            $question = htmlspecialchars($item['question'] ?? '');
            $answer = htmlspecialchars($item['answer'] ?? '');
            
            $html .= '<details class="border border-gray-200 rounded-lg group">
                <summary class="p-5 cursor-pointer font-semibold hover:bg-gray-50 list-none flex justify-between items-center">
                    ' . $question . '
                    <span class="text-gray-400">▼</span>
                </summary>
                <p class="px-5 pb-5 text-gray-600">' . $answer . '</p>
            </details>';
        }
        
        $html .= '</div>
    </div>
</section>';
        
        return $html;
    }

    private function generatePricingSection(array $content, string $style): string
    {
        $title = htmlspecialchars($content['title'] ?? 'Pricing');
        $subtitle = htmlspecialchars($content['subtitle'] ?? '');
        $plans = $content['plans'] ?? [];
        
        $html = '<section class="pricing-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-4">' . $title . '</h2>
        ' . ($subtitle ? '<p class="text-center text-gray-600 mb-12">' . $subtitle . '</p>' : '') . '
        <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">';
        
        foreach ($plans as $plan) {
            $name = htmlspecialchars($plan['name'] ?? '');
            $price = htmlspecialchars($plan['price'] ?? '');
            $features = $plan['features'] ?? [];
            $buttonText = htmlspecialchars($plan['buttonText'] ?? 'Get Started');
            $popular = $plan['popular'] ?? false;
            
            $borderClass = $popular ? 'border-2 border-blue-500' : 'border border-gray-200';
            $buttonClass = $popular ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-100 text-gray-900 hover:bg-gray-200';
            
            $html .= '<div class="bg-white p-6 rounded-lg shadow-sm ' . $borderClass . '">';
            
            if ($popular) {
                $html .= '<div class="text-center mb-4">
                    <span class="bg-blue-100 text-blue-600 text-xs font-semibold px-3 py-1 rounded-full">Most Popular</span>
                </div>';
            }
            
            $html .= '<h3 class="text-xl font-semibold text-center mb-2">' . $name . '</h3>
                <p class="text-4xl font-bold text-center mb-6">' . $price . '</p>
                <ul class="space-y-3 mb-6">';
            
            foreach ($features as $feature) {
                $html .= '<li class="flex items-start gap-2">
                    <span class="text-green-500">✓</span>
                    <span class="text-gray-600">' . htmlspecialchars($feature) . '</span>
                </li>';
            }
            
            $html .= '</ul>
                <button class="w-full py-3 rounded-lg font-semibold transition-colors ' . $buttonClass . '">
                    ' . $buttonText . '
                </button>
            </div>';
        }
        
        $html .= '</div>
    </div>
</section>';
        
        return $html;
    }

    private function generateProductsSection(array $content, string $style): string
    {
        $title = htmlspecialchars($content['title'] ?? 'Our Products');
        $subtitle = htmlspecialchars($content['subtitle'] ?? '');
        
        return '<section class="products-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-4">' . $title . '</h2>
        ' . ($subtitle ? '<p class="text-center text-gray-600 mb-12">' . $subtitle . '</p>' : '') . '
        <div class="text-center py-12 text-gray-500">
            <p>Products will be displayed here when integrated with your e-commerce system.</p>
        </div>
    </div>
</section>';
    }

    private function generateTeamSection(array $content, string $style): string
    {
        $layout = $content['layout'] ?? 'grid';
        $title = htmlspecialchars($content['title'] ?? 'Our Team');
        $items = $content['items'] ?? [];
        
        // Social Links Layout
        if ($layout === 'social') {
            $html = '<section class="team-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">' . $title . '</h2>
        <div class="grid md:grid-cols-4 gap-8">';
            
            foreach ($items as $member) {
                $name = htmlspecialchars($member['name'] ?? '');
                $role = htmlspecialchars($member['role'] ?? '');
                $bio = htmlspecialchars($member['bio'] ?? '');
                $image = $this->processMediaUrl($member['image'] ?? '');
                $initial = substr($name, 0, 1);
                
                $html .= '<div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 text-center">
                    <div class="w-24 h-24 mx-auto mb-4 rounded-full overflow-hidden bg-gray-200">';
                
                if ($image) {
                    $html .= '<img src="' . $image . '" alt="' . $name . '" class="w-full h-full object-cover">';
                } else {
                    $html .= '<div class="w-full h-full flex items-center justify-center bg-blue-100">
                        <span class="text-blue-600 font-bold text-2xl">' . $initial . '</span>
                    </div>';
                }
                
                $html .= '</div>
                    <h3 class="font-semibold text-lg">' . $name . '</h3>
                    <p class="text-gray-500 text-sm mb-3">' . $role . '</p>';
                
                if ($bio) {
                    $html .= '<p class="text-gray-600 text-sm mb-4">' . $bio . '</p>';
                }
                
                $html .= '</div>';
            }
            
            $html .= '</div>
    </div>
</section>';
            return $html;
        }
        
        // Compact List Layout
        if ($layout === 'compact') {
            $html = '<section class="team-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">' . $title . '</h2>
        <div class="max-w-4xl mx-auto space-y-4">';
            
            foreach ($items as $member) {
                $name = htmlspecialchars($member['name'] ?? '');
                $role = htmlspecialchars($member['role'] ?? '');
                $image = $this->processMediaUrl($member['image'] ?? '');
                $initial = substr($name, 0, 1);
                
                $html .= '<div class="flex items-center gap-4 p-4 bg-white rounded-lg border border-gray-100">
                    <div class="w-14 h-14 rounded-full overflow-hidden bg-gray-200 flex-shrink-0">';
                
                if ($image) {
                    $html .= '<img src="' . $image . '" alt="' . $name . '" class="w-full h-full object-cover">';
                } else {
                    $html .= '<div class="w-full h-full flex items-center justify-center bg-blue-100">
                        <span class="text-blue-600 font-bold">' . $initial . '</span>
                    </div>';
                }
                
                $html .= '</div>
                    <div class="flex-1">
                        <h3 class="font-semibold">' . $name . '</h3>
                        <p class="text-gray-500 text-sm">' . $role . '</p>
                    </div>
                </div>';
            }
            
            $html .= '</div>
    </div>
</section>';
            return $html;
        }
        
        // Default Grid Layout
        $html = '<section class="team-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">' . $title . '</h2>
        <div class="grid md:grid-cols-4 gap-8">';
        
        foreach ($items as $member) {
            $name = htmlspecialchars($member['name'] ?? '');
            $role = htmlspecialchars($member['role'] ?? '');
            $bio = htmlspecialchars($member['bio'] ?? '');
            $image = $this->processMediaUrl($member['image'] ?? '');
            $initial = substr($name, 0, 1);
            
            $html .= '<div class="text-center">
                <div class="w-32 h-32 mx-auto mb-4 rounded-full overflow-hidden bg-gray-200">';
            
            if ($image) {
                $html .= '<img src="' . $image . '" alt="' . $name . '" class="w-full h-full object-cover">';
            } else {
                $html .= '<div class="w-full h-full flex items-center justify-center bg-blue-100">
                    <span class="text-blue-600 font-bold text-3xl">' . $initial . '</span>
                </div>';
            }
            
            $html .= '</div>
                <h3 class="font-semibold text-lg">' . $name . '</h3>
                <p class="text-gray-500 text-sm">' . $role . '</p>';
            
            if ($bio) {
                $html .= '<p class="text-sm text-gray-600 mt-2">' . $bio . '</p>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>
    </div>
</section>';
        
        return $html;
    }

    private function generateTimelineSection(array $content, string $style): string
    {
        $title = htmlspecialchars($content['title'] ?? 'Timeline');
        $subtitle = htmlspecialchars($content['subtitle'] ?? '');
        $items = $content['items'] ?? [];
        
        $html = '<section class="timeline-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold mb-3">' . $title . '</h2>
            ' . ($subtitle ? '<p class="text-gray-600">' . $subtitle . '</p>' : '') . '
        </div>
        <div class="max-w-4xl mx-auto relative">
            <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-blue-200 hidden md:block"></div>
            <div class="space-y-8">';
        
        foreach ($items as $index => $item) {
            $itemTitle = htmlspecialchars($item['title'] ?? '');
            $date = htmlspecialchars($item['date'] ?? '');
            $description = htmlspecialchars($item['description'] ?? '');
            
            $html .= '<div class="relative flex gap-6">
                    <div class="flex-shrink-0 w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg z-10">
                        ' . ($index + 1) . '
                    </div>
                    <div class="flex-1 pb-8">
                        <h3 class="text-xl font-bold mb-2">' . $itemTitle . '</h3>
                        ' . ($date ? '<p class="text-sm text-gray-500 mb-2">' . $date . '</p>' : '') . '
                        <p class="text-gray-600">' . $description . '</p>
                    </div>
                </div>';
        }
        
        $html .= '</div>
        </div>
    </div>
</section>';
        
        return $html;
    }

    private function generateVideoHeroSection(array $content, string $style): string
    {
        $title = htmlspecialchars($content['title'] ?? '');
        $subtitle = htmlspecialchars($content['subtitle'] ?? '');
        $buttonText = htmlspecialchars($content['buttonText'] ?? '');
        $buttonLink = htmlspecialchars($content['buttonLink'] ?? '#');
        $secondaryButtonText = htmlspecialchars($content['secondaryButtonText'] ?? '');
        $secondaryButtonLink = htmlspecialchars($content['secondaryButtonLink'] ?? '#');
        $videoUrl = $this->processMediaUrl($content['videoUrl'] ?? '');
        
        return '<section class="video-hero-section relative overflow-hidden" style="min-height: 600px; ' . $style . '">
    ' . ($videoUrl ? '<video autoplay loop muted playsinline class="absolute inset-0 w-full h-full object-cover">
        <source src="' . $videoUrl . '" type="video/mp4">
    </video>' : '') . '
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative z-10 h-full flex flex-col justify-center items-center text-center px-4 py-20">
        <h1 class="text-5xl font-bold mb-4 text-white">' . $title . '</h1>
        ' . ($subtitle ? '<p class="text-xl mb-8 text-white/90 max-w-2xl">' . $subtitle . '</p>' : '') . '
        <div class="flex gap-4">
            ' . ($buttonText ? '<a href="' . $buttonLink . '" class="px-8 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors">' . $buttonText . '</a>' : '') . '
            ' . ($secondaryButtonText ? '<a href="' . $secondaryButtonLink . '" class="px-8 py-3 bg-transparent border-2 border-white text-white font-semibold rounded-lg hover:bg-white/10 transition-colors">' . $secondaryButtonText . '</a>' : '') . '
        </div>
    </div>
</section>';
    }

    private function generateLogoCloudSection(array $content, string $style): string
    {
        $title = htmlspecialchars($content['title'] ?? '');
        $subtitle = htmlspecialchars($content['subtitle'] ?? '');
        $logos = $content['logos'] ?? [];
        
        $html = '<section class="logo-cloud-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4 text-center">
        ' . ($title ? '<h2 class="text-2xl font-bold mb-2">' . $title . '</h2>' : '') . '
        ' . ($subtitle ? '<p class="text-gray-600 mb-12">' . $subtitle . '</p>' : '') . '
        <div class="flex flex-wrap justify-center items-center gap-12 opacity-60">';
        
        foreach ($logos as $logo) {
            $name = htmlspecialchars($logo['name'] ?? '');
            $image = $this->processMediaUrl($logo['image'] ?? '');
            
            if ($image) {
                $html .= '<img src="' . $image . '" alt="' . $name . '" class="h-12 grayscale hover:grayscale-0 transition-all">';
            } else {
                $html .= '<div class="text-3xl font-bold text-gray-400">' . $name . '</div>';
            }
        }
        
        $html .= '</div>
    </div>
</section>';
        
        return $html;
    }

    private function generateMapSection(array $content, string $style): string
    {
        $title = htmlspecialchars($content['title'] ?? '');
        $embedUrl = htmlspecialchars($content['embedUrl'] ?? '');
        $address = htmlspecialchars($content['address'] ?? '');
        $showAddress = $content['showAddress'] ?? false;
        
        return '<section class="map-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        ' . ($title ? '<h2 class="text-3xl font-bold text-center mb-8">' . $title . '</h2>' : '') . '
        <div class="max-w-4xl mx-auto">
            <div class="aspect-video rounded-lg overflow-hidden bg-gray-100">';
        
        if ($embedUrl) {
            return $html . '<iframe src="' . $embedUrl . '" class="w-full h-full" frameborder="0" allowfullscreen></iframe>';
        } else {
            return $html . '<div class="w-full h-full flex items-center justify-center text-gray-400">
                    <span class="text-4xl">🗺️</span>
                </div>';
        }
        
        $html .= '</div>
            ' . ($showAddress && $address ? '<p class="text-center mt-4 text-gray-600">📍 ' . $address . '</p>' : '') . '
        </div>
    </div>
</section>';
        
        return $html;
    }

    private function generateVideoSection(array $content, string $style): string
    {
        $title = htmlspecialchars($content['title'] ?? '');
        $description = htmlspecialchars($content['description'] ?? '');
        $videoUrl = htmlspecialchars($content['videoUrl'] ?? '');
        
        return '<section class="video-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            ' . ($title ? '<h2 class="text-3xl font-bold mb-4">' . $title . '</h2>' : '') . '
            ' . ($description ? '<p class="text-gray-600 mb-8">' . $description . '</p>' : '') . '
            <div class="aspect-video rounded-lg overflow-hidden bg-gray-900">';
        
        if ($videoUrl) {
            return $html . '<iframe src="' . $videoUrl . '" class="w-full h-full" frameborder="0" allowfullscreen></iframe>';
        } else {
            return $html . '<div class="w-full h-full flex items-center justify-center text-gray-500">
                    <span class="text-5xl">▶️</span>
                </div>';
        }
        
        $html .= '</div>
        </div>
    </div>
</section>';
        
        return $html;
    }

    private function generateBlogSection(array $content, string $style): string
    {
        $title = htmlspecialchars($content['title'] ?? 'Latest Posts');
        $posts = $content['posts'] ?? [];
        
        $html = '<section class="blog-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">' . $title . '</h2>
        <div class="grid md:grid-cols-3 gap-8">';
        
        foreach ($posts as $post) {
            $postTitle = htmlspecialchars($post['title'] ?? '');
            $excerpt = htmlspecialchars($post['excerpt'] ?? '');
            $date = htmlspecialchars($post['date'] ?? '');
            $image = $this->processMediaUrl($post['image'] ?? '');
            
            $html .= '<article class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">
                <div class="aspect-video bg-gray-100">';
            
            if ($image) {
                $html .= '<img src="' . $image . '" alt="' . $postTitle . '" class="w-full h-full object-cover">';
            } else {
                $html .= '<div class="w-full h-full flex items-center justify-center text-gray-300">
                    <span class="text-4xl">📰</span>
                </div>';
            }
            
            $html .= '</div>
                <div class="p-6">
                    ' . ($date ? '<p class="text-sm text-gray-500 mb-2">' . $date . '</p>' : '') . '
                    <h3 class="font-semibold text-lg mb-2">' . $postTitle . '</h3>
                    <p class="text-gray-600 text-sm">' . $excerpt . '</p>
                </div>
            </article>';
        }
        
        $html .= '</div>
    </div>
</section>';
        
        return $html;
    }

    private function generateCTABannerSection(array $content, string $style): string
    {
        $title = htmlspecialchars($content['title'] ?? '');
        $description = htmlspecialchars($content['description'] ?? '');
        $buttonText = htmlspecialchars($content['buttonText'] ?? '');
        $buttonLink = htmlspecialchars($content['buttonLink'] ?? '#');
        
        return '<section class="cta-banner-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl font-bold mb-4">' . $title . '</h2>
            ' . ($description ? '<p class="text-lg mb-6 opacity-90">' . $description . '</p>' : '') . '
            ' . ($buttonText ? '<a href="' . $buttonLink . '" class="inline-block px-8 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors">' . $buttonText . '</a>' : '') . '
        </div>
    </div>
</section>';
    }

    /**
     * Generate footer HTML
     */
    private function generateFooter(GrowBuilderSite $site, array $footer): string
    {
        $copyrightText = htmlspecialchars($footer['copyrightText'] ?? '© ' . date('Y') . ' ' . $site->name);
        $columns = $footer['columns'] ?? [];
        
        $html = '<footer class="bg-gray-900 text-white py-12">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-' . count($columns) . ' gap-8 mb-8">';
        
        foreach ($columns as $column) {
            $title = htmlspecialchars($column['title'] ?? '');
            $links = $column['links'] ?? [];
            
            $html .= '<div>
                <h3 class="font-bold mb-4">' . $title . '</h3>
                <ul class="space-y-2">';
            
            foreach ($links as $link) {
                $label = htmlspecialchars($link['label'] ?? '');
                $url = $link['url'] === '/' ? 'index.html' : ltrim($link['url'], '/') . '.html';
                
                $html .= '<li><a href="' . $url . '" class="hover:text-blue-400">' . $label . '</a></li>';
            }
            
            $html .= '</ul>
            </div>';
        }
        
        $html .= '</div>
        <div class="border-t border-gray-700 pt-8 text-center">
            <p>' . $copyrightText . '</p>
            <p class="text-sm text-gray-400 mt-2">Powered by <a href="https://mygrownet.com" class="text-blue-400 hover:underline">GrowBuilder</a></p>
        </div>
    </div>
</footer>';
        
        return $html;
    }

    /**
     * Process media URL (download and return local path)
     */
    private function processMediaUrl(string $url): string
    {
        if (empty($url)) {
            return '';
        }
        
        // If already processed, return cached path
        if (isset($this->downloadedMedia[$url])) {
            return $this->downloadedMedia[$url];
        }
        
        // Download media file
        try {
            $filename = basename(parse_url($url, PHP_URL_PATH));
            $localPath = 'media/' . $filename;
            $fullPath = $this->tempDir . '/' . $localPath;
            
            // Download file
            $content = file_get_contents($url);
            if ($content !== false) {
                file_put_contents($fullPath, $content);
                $this->downloadedMedia[$url] = $localPath;
                return $localPath;
            }
        } catch (\Exception $e) {
            // If download fails, return original URL
            return $url;
        }
        
        return $url;
    }

    /**
     * Copy media files from site
     */
    private function copyMediaFiles(GrowBuilderSite $site): void
    {
        $media = $site->media()->get();
        
        foreach ($media as $mediaItem) {
            try {
                $url = $mediaItem->url;
                $this->processMediaUrl($url);
            } catch (\Exception $e) {
                // Skip failed downloads
                continue;
            }
        }
        
        // Copy logo and favicon
        if ($site->logo) {
            $this->processMediaUrl($site->logo);
        }
        if ($site->favicon) {
            $this->processMediaUrl($site->favicon);
        }
    }

    /**
     * Generate CSS file
     */
    private function generateCSS(GrowBuilderSite $site): void
    {
        $theme = $site->theme ?? [];
        $primaryColor = $theme['primaryColor'] ?? '#2563eb';
        $secondaryColor = $theme['secondaryColor'] ?? '#64748b';
        $accentColor = $theme['accentColor'] ?? '#059669';
        $backgroundColor = $theme['backgroundColor'] ?? '#ffffff';
        $textColor = $theme['textColor'] ?? '#1f2937';
        $headingFont = $theme['headingFont'] ?? 'Inter';
        $bodyFont = $theme['bodyFont'] ?? 'Inter';
        $borderRadius = $theme['borderRadius'] ?? 8;
        
        $css = "/* Generated by GrowBuilder - https://mygrownet.com */
@import url('https://fonts.googleapis.com/css2?family={$headingFont}:wght@400;600;700&family={$bodyFont}:wght@400;500&display=swap');

:root {
    --primary-color: {$primaryColor};
    --secondary-color: {$secondaryColor};
    --accent-color: {$accentColor};
    --background-color: {$backgroundColor};
    --text-color: {$textColor};
    --heading-font: '{$headingFont}', sans-serif;
    --body-font: '{$bodyFont}', sans-serif;
    --border-radius: {$borderRadius}px;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: var(--body-font);
    color: var(--text-color);
    background-color: var(--background-color);
    line-height: 1.6;
}

h1, h2, h3, h4, h5, h6 {
    font-family: var(--heading-font);
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 1rem;
}

h1 { font-size: 3rem; }
h2 { font-size: 2.25rem; }
h3 { font-size: 1.875rem; }

a {
    color: var(--primary-color);
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

/* Layout */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.max-w-xl { max-width: 36rem; margin-left: auto; margin-right: auto; }
.max-w-2xl { max-width: 42rem; margin-left: auto; margin-right: auto; }
.max-w-3xl { max-width: 48rem; margin-left: auto; margin-right: auto; }
.max-w-4xl { max-width: 56rem; margin-left: auto; margin-right: auto; }
.max-w-5xl { max-width: 64rem; margin-left: auto; margin-right: auto; }
.max-w-6xl { max-width: 72rem; margin-left: auto; margin-right: auto; }
.mx-auto { margin-left: auto; margin-right: auto; }
.ml-auto { margin-left: auto; }
.mr-auto { margin-right: auto; }

/* Flexbox */
.flex { display: flex; }
.flex-1 { flex: 1 1 0%; }
.flex-shrink-0 { flex-shrink: 0; }
.flex-wrap { flex-wrap: wrap; }
.items-center { align-items: center; }
.items-start { align-items: flex-start; }
.items-end { align-items: flex-end; }
.justify-between { justify-content: space-between; }
.justify-center { justify-content: center; }
.justify-end { justify-content: flex-end; }
.space-x-2 > * + * { margin-left: 0.5rem; }
.space-x-3 > * + * { margin-left: 0.75rem; }
.space-x-4 > * + * { margin-left: 1rem; }
.space-x-6 > * + * { margin-left: 1.5rem; }
.space-x-8 > * + * { margin-left: 2rem; }
.space-x-12 > * + * { margin-left: 3rem; }
.space-y-1 > * + * { margin-top: 0.25rem; }
.space-y-2 > * + * { margin-top: 0.5rem; }
.space-y-3 > * + * { margin-top: 0.75rem; }
.space-y-4 > * + * { margin-top: 1rem; }
.space-y-6 > * + * { margin-top: 1.5rem; }
.space-y-8 > * + * { margin-top: 2rem; }
.gap-2 { gap: 0.5rem; }
.gap-3 { gap: 0.75rem; }
.gap-4 { gap: 1rem; }
.gap-6 { gap: 1.5rem; }
.gap-8 { gap: 2rem; }
.gap-12 { gap: 3rem; }

/* Grid */
.grid { display: grid; }
.grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
.grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
.grid-cols-5 { grid-template-columns: repeat(5, minmax(0, 1fr)); }
.grid-cols-6 { grid-template-columns: repeat(6, minmax(0, 1fr)); }

/* Display */
.hidden { display: none; }
.block { display: block; }
.inline-block { display: inline-block; }
.inline { display: inline; }
.inline-flex { display: inline-flex; }

/* Positioning */
.relative { position: relative; }
.absolute { position: absolute; }
.fixed { position: fixed; }
.sticky { position: sticky; }
.inset-0 { top: 0; right: 0; bottom: 0; left: 0; }
.top-0 { top: 0; }
.right-0 { right: 0; }
.bottom-0 { bottom: 0; }
.left-0 { left: 0; }
.top-2 { top: 0.5rem; }
.left-2 { left: 0.5rem; }
.left-6 { left: 1.5rem; }
.-top-1 { top: -0.25rem; }
.-right-1 { right: -0.25rem; }
.z-10 { z-index: 10; }
.z-50 { z-index: 50; }

/* Spacing */
.px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
.px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
.px-4 { padding-left: 1rem; padding-right: 1rem; }
.px-5 { padding-left: 1.25rem; padding-right: 1.25rem; }
.px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
.px-8 { padding-left: 2rem; padding-right: 2rem; }
.py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
.py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
.py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
.py-4 { padding-top: 1rem; padding-bottom: 1rem; }
.py-5 { padding-top: 1.25rem; padding-bottom: 1.25rem; }
.py-6 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
.py-8 { padding-top: 2rem; padding-bottom: 2rem; }
.py-12 { padding-top: 3rem; padding-bottom: 3rem; }
.py-16 { padding-top: 4rem; padding-bottom: 4rem; }
.py-20 { padding-top: 5rem; padding-bottom: 5rem; }
.pt-2 { padding-top: 0.5rem; }
.pt-3 { padding-top: 0.75rem; }
.pt-8 { padding-top: 2rem; }
.pb-3 { padding-bottom: 0.75rem; }
.pb-6 { padding-bottom: 1.5rem; }
.pb-8 { padding-bottom: 2rem; }
.p-2 { padding: 0.5rem; }
.p-3 { padding: 0.75rem; }
.p-4 { padding: 1rem; }
.p-5 { padding: 1.25rem; }
.p-6 { padding: 1.5rem; }
.p-8 { padding: 2rem; }

.m-0 { margin: 0; }
.mx-auto { margin-left: auto; margin-right: auto; }
.mb-1 { margin-bottom: 0.25rem; }
.mb-2 { margin-bottom: 0.5rem; }
.mb-3 { margin-bottom: 0.75rem; }
.mb-4 { margin-bottom: 1rem; }
.mb-6 { margin-bottom: 1.5rem; }
.mb-8 { margin-bottom: 2rem; }
.mb-12 { margin-bottom: 3rem; }
.mt-2 { margin-top: 0.5rem; }
.mt-4 { margin-top: 1rem; }
.mt-8 { margin-top: 2rem; }
.mr-2 { margin-right: 0.5rem; }
.ml-2 { margin-left: 0.5rem; }

/* Sizing */
.w-2 { width: 0.5rem; }
.w-4 { width: 1rem; }
.w-5 { width: 1.25rem; }
.w-6 { width: 1.5rem; }
.w-8 { width: 2rem; }
.w-10 { width: 2.5rem; }
.w-12 { width: 3rem; }
.w-14 { width: 3.5rem; }
.w-16 { width: 4rem; }
.w-20 { width: 5rem; }
.w-24 { width: 6rem; }
.w-32 { width: 8rem; }
.w-full { width: 100%; }
.w-1\\/2 { width: 50%; }

.h-2 { height: 0.5rem; }
.h-4 { height: 1rem; }
.h-5 { height: 1.25rem; }
.h-6 { height: 1.5rem; }
.h-8 { height: 2rem; }
.h-10 { height: 2.5rem; }
.h-12 { height: 3rem; }
.h-14 { height: 3.5rem; }
.h-16 { height: 4rem; }
.h-20 { height: 5rem; }
.h-24 { height: 6rem; }
.h-32 { height: 8rem; }
.h-48 { height: 12rem; }
.h-64 { height: 16rem; }
.h-full { height: 100%; }
.min-h-screen { min-height: 100vh; }

/* Aspect Ratio */
.aspect-square { aspect-ratio: 1 / 1; }
.aspect-video { aspect-ratio: 16 / 9; }

/* Typography */
.text-xs { font-size: 0.75rem; line-height: 1rem; }
.text-sm { font-size: 0.875rem; line-height: 1.25rem; }
.text-base { font-size: 1rem; line-height: 1.5rem; }
.text-lg { font-size: 1.125rem; line-height: 1.75rem; }
.text-xl { font-size: 1.25rem; line-height: 1.75rem; }
.text-2xl { font-size: 1.5rem; line-height: 2rem; }
.text-3xl { font-size: 1.875rem; line-height: 2.25rem; }
.text-4xl { font-size: 2.25rem; line-height: 2.5rem; }
.text-5xl { font-size: 3rem; line-height: 1; }
.font-normal { font-weight: 400; }
.font-medium { font-weight: 500; }
.font-semibold { font-weight: 600; }
.font-bold { font-weight: 700; }
.italic { font-style: italic; }
.text-left { text-align: left; }
.text-center { text-align: center; }
.text-right { text-align: right; }
.uppercase { text-transform: uppercase; }
.lowercase { text-transform: lowercase; }
.capitalize { text-transform: capitalize; }
.underline { text-decoration: underline; }
.line-through { text-decoration: line-through; }
.no-underline { text-decoration: none; }
.line-clamp-2 { 
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.line-clamp-3 { 
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Colors */
.text-white { color: #ffffff; }
.text-black { color: #000000; }
.text-gray-50 { color: #f9fafb; }
.text-gray-100 { color: #f3f4f6; }
.text-gray-200 { color: #e5e7eb; }
.text-gray-300 { color: #d1d5db; }
.text-gray-400 { color: #9ca3af; }
.text-gray-500 { color: #6b7280; }
.text-gray-600 { color: #4b5563; }
.text-gray-700 { color: #374151; }
.text-gray-800 { color: #1f2937; }
.text-gray-900 { color: #111827; }
.text-blue-50 { color: #eff6ff; }
.text-blue-100 { color: #dbeafe; }
.text-blue-400 { color: #60a5fa; }
.text-blue-500 { color: #3b82f6; }
.text-blue-600 { color: #2563eb; }
.text-blue-700 { color: #1d4ed8; }
.text-green-500 { color: #10b981; }
.text-green-600 { color: #059669; }
.text-red-500 { color: #ef4444; }
.text-red-600 { color: #dc2626; }

.bg-white { background-color: #ffffff; }
.bg-black { background-color: #000000; }
.bg-transparent { background-color: transparent; }
.bg-gray-50 { background-color: #f9fafb; }
.bg-gray-100 { background-color: #f3f4f6; }
.bg-gray-200 { background-color: #e5e7eb; }
.bg-gray-700 { background-color: #374151; }
.bg-gray-900 { background-color: #111827; }
.bg-blue-50 { background-color: #eff6ff; }
.bg-blue-100 { background-color: #dbeafe; }
.bg-blue-500 { background-color: #3b82f6; }
.bg-blue-600 { background-color: #2563eb; }
.bg-red-100 { background-color: #fee2e2; }
.bg-red-500 { background-color: #ef4444; }

.hover\\:bg-white:hover { background-color: #ffffff; }
.hover\\:bg-gray-50:hover { background-color: #f9fafb; }
.hover\\:bg-gray-100:hover { background-color: #f3f4f6; }
.hover\\:bg-gray-200:hover { background-color: #e5e7eb; }
.hover\\:bg-blue-50:hover { background-color: #eff6ff; }
.hover\\:bg-blue-700:hover { background-color: #1d4ed8; }
.hover\\:bg-white\\/10:hover { background-color: rgba(255, 255, 255, 0.1); }
.hover\\:bg-white\\/20:hover { background-color: rgba(255, 255, 255, 0.2); }

.hover\\:text-gray-900:hover { color: #111827; }
.hover\\:text-blue-600:hover { color: #2563eb; }
.hover\\:text-blue-700:hover { color: #1d4ed8; }
.hover\\:text-blue-400:hover { color: #60a5fa; }
.hover\\:underline:hover { text-decoration: underline; }

.opacity-50 { opacity: 0.5; }
.opacity-60 { opacity: 0.6; }
.opacity-90 { opacity: 0.9; }
.text-white\\/90 { color: rgba(255, 255, 255, 0.9); }
.text-white\\/80 { color: rgba(255, 255, 255, 0.8); }

.grayscale { filter: grayscale(100%); }
.hover\\:grayscale-0:hover { filter: grayscale(0%); }

/* Borders */
.border { border-width: 1px; }
.border-0 { border-width: 0; }
.border-2 { border-width: 2px; }
.border-t { border-top-width: 1px; }
.border-b { border-bottom-width: 1px; }
.border-l { border-left-width: 1px; }
.border-r { border-right-width: 1px; }
.border-gray-100 { border-color: #f3f4f6; }
.border-gray-200 { border-color: #e5e7eb; }
.border-gray-300 { border-color: #d1d5db; }
.border-gray-700 { border-color: #374151; }
.border-blue-200 { border-color: #bfdbfe; }
.border-blue-500 { border-color: #3b82f6; }
.border-blue-600 { border-color: #2563eb; }
.border-white { border-color: #ffffff; }
.rounded { border-radius: 0.25rem; }
.rounded-sm { border-radius: 0.125rem; }
.rounded-md { border-radius: 0.375rem; }
.rounded-lg { border-radius: var(--border-radius); }
.rounded-xl { border-radius: 0.75rem; }
.rounded-full { border-radius: 9999px; }
.rounded-t-lg { border-top-left-radius: var(--border-radius); border-top-right-radius: var(--border-radius); }

/* Shadows */
.shadow { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); }
.shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
.shadow-md { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
.shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
.shadow-xl { box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
.shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
.hover\\:shadow-md:hover { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
.hover\\:shadow-lg:hover { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }

/* Overflow */
.overflow-hidden { overflow: hidden; }
.overflow-auto { overflow: auto; }
.overflow-x-auto { overflow-x: auto; }
.overflow-y-auto { overflow-y: auto; }

/* Cursor */
.cursor-pointer { cursor: pointer; }
.cursor-not-allowed { cursor: not-allowed; }

/* List Style */
.list-none { list-style-type: none; }

/* Transitions */
.transition { transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
.transition-all { transition-property: all; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
.transition-colors { transition-property: color, background-color, border-color, text-decoration-color, fill, stroke; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
.transition-transform { transition-property: transform; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
.duration-150 { transition-duration: 150ms; }
.duration-200 { transition-duration: 200ms; }
.duration-300 { transition-duration: 300ms; }

/* Transform */
.scale-105 { transform: scale(1.05); }
.hover\\:scale-105:hover { transform: scale(1.05); }
.hover\\:scale-110:hover { transform: scale(1.1); }
.-translate-y-1\\/2 { transform: translateY(-50%); }
.rotate-180 { transform: rotate(180deg); }
.group-open\\:rotate-180:is(:where(.group):is([open], [open] *)) { transform: rotate(180deg); }

/* Buttons */
.btn-primary {
    display: inline-block;
    padding: 0.75rem 2rem;
    background-color: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: var(--border-radius);
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

/* Navigation */
nav {
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Cards */
.service-card, .testimonial-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.service-card:hover, .testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

/* Slideshow */
.hero-slideshow {
    position: relative;
    min-height: 500px;
}

.slideshow-container {
    position: relative;
    width: 100%;
    height: 100%;
}

.slide {
    display: none;
    width: 100%;
    min-height: 500px;
    position: relative;
}

.slide.active {
    display: block;
}

.slideshow-dots {
    text-align: center;
    position: absolute;
    bottom: 20px;
    width: 100%;
    z-index: 10;
}

.dot {
    cursor: pointer;
    height: 12px;
    width: 12px;
    margin: 0 5px;
    background-color: rgba(255, 255, 255, 0.5);
    border-radius: 50%;
    display: inline-block;
    transition: background-color 0.3s ease;
}

.dot.active,
.dot:hover {
    background-color: rgba(255, 255, 255, 0.9);
}

/* Images */
img {
    max-width: 100%;
    height: auto;
}

/* Prose */
.prose {
    max-width: 65ch;
}

.prose p {
    margin-bottom: 1rem;
}

/* Forms */
input, textarea, select {
    font-family: inherit;
    font-size: inherit;
}

input:focus, textarea:focus, select:focus {
    outline: none;
}

.focus\\:ring-2:focus {
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.5);
}

.focus\\:border-transparent:focus {
    border-color: transparent;
}

/* Footer */
footer {
    background-color: #111827;
    color: white;
}

footer a {
    color: white;
    text-decoration: none;
    transition: color 0.2s ease;
}

footer a:hover {
    color: #60a5fa;
}

/* Responsive */
@media (min-width: 640px) {
    .sm\\:px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
    .sm\\:py-16 { padding-top: 4rem; padding-bottom: 4rem; }
    .sm\\:text-3xl { font-size: 1.875rem; line-height: 2.25rem; }
    .sm\\:text-4xl { font-size: 2.25rem; line-height: 2.5rem; }
    .sm\\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .sm\\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}

@media (min-width: 768px) {
    .md\\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .md\\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .md\\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .md\\:grid-cols-5 { grid-template-columns: repeat(5, minmax(0, 1fr)); }
    .md\\:grid-cols-6 { grid-template-columns: repeat(6, minmax(0, 1fr)); }
    .md\\:flex { display: flex; }
    .md\\:hidden { display: none; }
    .md\\:block { display: block; }
    .md\\:px-8 { padding-left: 2rem; padding-right: 2rem; }
}

@media (min-width: 1024px) {
    .lg\\:px-8 { padding-left: 2rem; padding-right: 2rem; }
    .lg\\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .lg\\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .lg\\:text-5xl { font-size: 3rem; line-height: 1; }
}

@media (max-width: 768px) {
    h1 { font-size: 2rem; }
    h2 { font-size: 1.75rem; }
    h3 { font-size: 1.5rem; }
    
    .container {
        padding: 0 1rem;
    }
    
    .hidden.md\\:hidden { display: block; }
}
";
        
        file_put_contents($this->tempDir . '/css/styles.css', $css);
    }

    /**
     * Generate JavaScript file
     */
    private function generateJS(GrowBuilderSite $site): void
    {
        $js = "// Generated by GrowBuilder - https://mygrownet.com

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const menuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (menuButton && mobileMenu) {
        menuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^=\"#\"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Slideshow functionality
    initSlideshow();
});

// Slideshow
let slideIndex = 1;
let slideTimer;

function initSlideshow() {
    const slides = document.querySelectorAll('.slide');
    if (slides.length > 0) {
        showSlide(slideIndex);
        // Auto-advance slides every 5 seconds
        slideTimer = setInterval(function() {
            plusSlides(1);
        }, 5000);
    }
}

function plusSlides(n) {
    clearInterval(slideTimer);
    showSlide(slideIndex += n);
    slideTimer = setInterval(function() {
        plusSlides(1);
    }, 5000);
}

function currentSlide(n) {
    clearInterval(slideTimer);
    showSlide(slideIndex = n);
    slideTimer = setInterval(function() {
        plusSlides(1);
    }, 5000);
}

function showSlide(n) {
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    
    if (slides.length === 0) return;
    
    if (n > slides.length) { slideIndex = 1; }
    if (n < 1) { slideIndex = slides.length; }
    
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    if (slides[slideIndex - 1]) {
        slides[slideIndex - 1].classList.add('active');
    }
    if (dots[slideIndex - 1]) {
        dots[slideIndex - 1].classList.add('active');
    }
}
";
        
        file_put_contents($this->tempDir . '/js/main.js', $js);
    }

    /**
     * Create README file
     */
    private function createReadme(GrowBuilderSite $site): void
    {
        $readme = "# {$site->name}

This is a static export of your GrowBuilder site.

## 📦 What's Included

- **HTML Pages**: All your site pages as static HTML files
- **CSS**: Compiled styles based on your theme
- **JavaScript**: Interactive features and navigation
- **Media**: All images and media files used on your site

## 🚀 How to Use

### Option 1: Open Locally
Simply open `index.html` in your web browser to view your site.

### Option 2: Deploy to Web Hosting
Upload all files to your web hosting provider:

1. **Netlify** (Free):
   - Drag and drop this folder to https://app.netlify.com/drop
   - Your site will be live instantly!

2. **Vercel** (Free):
   - Install Vercel CLI: `npm i -g vercel`
   - Run: `vercel` in this directory

3. **GitHub Pages** (Free):
   - Create a GitHub repository
   - Push these files
   - Enable GitHub Pages in repository settings

4. **Traditional Hosting** (cPanel, FTP):
   - Upload all files to your `public_html` or `www` directory
   - Access via your domain

## 📁 File Structure

```
/
├── index.html          # Homepage
├── about.html          # About page
├── contact.html        # Contact page
├── css/
│   └── styles.css      # Compiled styles
├── js/
│   └── main.js         # JavaScript functionality
├── images/             # Logo, favicon
└── media/              # All media files
```

## ⚙️ Customization

To customize your site:

1. Edit HTML files directly
2. Modify `css/styles.css` for styling changes
3. Update `js/main.js` for functionality changes

## 🔗 Forms & Dynamic Features

**Note**: Contact forms and dynamic features require backend integration.

For forms, you can use:
- **Formspree**: https://formspree.io (Free tier available)
- **Netlify Forms**: Built-in if hosted on Netlify
- **Google Forms**: Embed Google Forms

## 📝 License

This export was generated by GrowBuilder (https://mygrownet.com).
Content belongs to {$site->name}.

## 💡 Need Help?

- GrowBuilder Documentation: https://mygrownet.com/docs
- Support: support@mygrownet.com

---

**Powered by GrowBuilder** - Build beautiful websites in minutes
https://mygrownet.com
";
        
        file_put_contents($this->tempDir . '/README.md', $readme);
    }

    /**
     * Create ZIP file
     */
    private function createZip(GrowBuilderSite $site): string
    {
        $zipFilename = 'site-export-' . $site->subdomain . '-' . date('Y-m-d') . '.zip';
        $zipPath = storage_path('app/exports/' . $zipFilename);
        
        // Ensure exports directory exists
        if (!is_dir(storage_path('app/exports'))) {
            mkdir(storage_path('app/exports'), 0755, true);
        }
        
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Could not create ZIP file');
        }
        
        // Add all files to ZIP
        $this->addDirectoryToZip($zip, $this->tempDir, '');
        
        $zip->close();
        
        return $zipPath;
    }

    /**
     * Recursively add directory to ZIP
     */
    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $zipPath): void
    {
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $filePath = $dir . '/' . $file;
            $zipFilePath = $zipPath ? $zipPath . '/' . $file : $file;
            
            if (is_dir($filePath)) {
                $zip->addEmptyDir($zipFilePath);
                $this->addDirectoryToZip($zip, $filePath, $zipFilePath);
            } else {
                $zip->addFile($filePath, $zipFilePath);
            }
        }
    }

    /**
     * Cleanup temporary directory
     */
    private function cleanup(): void
    {
        if (is_dir($this->tempDir)) {
            $this->deleteDirectory($this->tempDir);
        }
    }

    /**
     * Recursively delete directory
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $filePath = $dir . '/' . $file;
            
            if (is_dir($filePath)) {
                $this->deleteDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }
        
        rmdir($dir);
    }
}
