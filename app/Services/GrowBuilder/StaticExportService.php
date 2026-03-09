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
        $this->tempDir = storage_path('app/temp/export-' . $site->id . '-' . time());
        
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
            'about' => $this->generateAboutSection($content, $styleAttr),
            'services' => $this->generateServicesSection($content, $styleAttr),
            'features' => $this->generateFeaturesSection($content, $styleAttr),
            'contact' => $this->generateContactSection($content, $styleAttr),
            'cta' => $this->generateCTASection($content, $styleAttr),
            'testimonials' => $this->generateTestimonialsSection($content, $styleAttr),
            'gallery' => $this->generateGallerySection($content, $styleAttr),
            'text' => $this->generateTextSection($content, $styleAttr),
            default => ''
        };
    }

    private function generateHeroSection(array $content, string $style): string
    {
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

    private function generateAboutSection(array $content, string $style): string
    {
        $title = htmlspecialchars($content['title'] ?? '');
        $description = htmlspecialchars($content['description'] ?? '');
        $image = $this->processMediaUrl($content['image'] ?? '');
        
        return '<section class="about-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-8 items-center">
            <div>
                <h2 class="text-3xl font-bold mb-4">' . $title . '</h2>
                <p class="text-lg">' . nl2br($description) . '</p>
            </div>
            ' . ($image ? '<div><img src="' . $image . '" alt="' . $title . '" class="rounded-lg shadow-lg"></div>' : '') . '
        </div>
    </div>
</section>';
    }

    private function generateServicesSection(array $content, string $style): string
    {
        $title = htmlspecialchars($content['title'] ?? '');
        $items = $content['items'] ?? [];
        
        $html = '<section class="services-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">' . $title . '</h2>
        <div class="grid md:grid-cols-3 gap-8">';
        
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
        return $this->generateServicesSection($content, $style); // Similar structure
    }

    private function generateContactSection(array $content, string $style): string
    {
        $title = htmlspecialchars($content['title'] ?? '');
        $description = htmlspecialchars($content['description'] ?? '');
        $email = htmlspecialchars($content['email'] ?? '');
        $phone = htmlspecialchars($content['phone'] ?? '');
        $address = htmlspecialchars($content['address'] ?? '');
        
        return '<section class="contact-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-4">' . $title . '</h2>
        <p class="text-center mb-12">' . $description . '</p>
        <div class="max-w-2xl mx-auto">
            <div class="space-y-4">
                ' . ($email ? '<p><strong>Email:</strong> <a href="mailto:' . $email . '">' . $email . '</a></p>' : '') . '
                ' . ($phone ? '<p><strong>Phone:</strong> <a href="tel:' . $phone . '">' . $phone . '</a></p>' : '') . '
                ' . ($address ? '<p><strong>Address:</strong> ' . nl2br($address) . '</p>' : '') . '
            </div>
        </div>
    </div>
</section>';
    }

    private function generateCTASection(array $content, string $style): string
    {
        $title = htmlspecialchars($content['title'] ?? '');
        $description = htmlspecialchars($content['description'] ?? '');
        $buttonText = htmlspecialchars($content['buttonText'] ?? '');
        $buttonLink = htmlspecialchars($content['buttonLink'] ?? '#');
        
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
        $title = htmlspecialchars($content['title'] ?? '');
        $items = $content['items'] ?? [];
        
        $html = '<section class="testimonials-section py-16" style="' . $style . '">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">' . $title . '</h2>
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

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

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

nav {
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

nav.sticky {
    position: sticky;
    top: 0;
    z-index: 50;
}

.service-card, .testimonial-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.service-card:hover, .testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

img {
    max-width: 100%;
    height: auto;
}

.prose {
    max-width: 65ch;
}

.prose p {
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    h1 { font-size: 2rem; }
    h2 { font-size: 1.75rem; }
    h3 { font-size: 1.5rem; }
    
    .container {
        padding: 0 1rem;
    }
}

/* Utility Classes */
.text-center { text-align: center; }
.mb-4 { margin-bottom: 1rem; }
.mb-8 { margin-bottom: 2rem; }
.mb-12 { margin-bottom: 3rem; }
.py-16 { padding-top: 4rem; padding-bottom: 4rem; }
.py-20 { padding-top: 5rem; padding-bottom: 5rem; }
.rounded-lg { border-radius: var(--border-radius); }
.shadow-md { box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.shadow-lg { box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
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
});
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
