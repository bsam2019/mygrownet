<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pages = \App\Models\GrowBuilder\SiteTemplatePage::all();
$sectionTypes = [];

foreach ($pages as $page) {
    $content = $page->content; // Use 'content' not 'content_json'
    if (isset($content['sections'])) {
        foreach ($content['sections'] as $section) {
            $type = $section['type'] ?? 'unknown';
            $layout = $section['content']['layout'] ?? 'default';
            $key = $type . '|' . $layout;
            if (!isset($sectionTypes[$key])) {
                $sectionTypes[$key] = 0;
            }
            $sectionTypes[$key]++;
        }
    }
}

echo "Section Types Across All Templates:\n\n";
ksort($sectionTypes);
foreach ($sectionTypes as $key => $count) {
    list($type, $layout) = explode('|', $key);
    echo str_pad($type, 20) . str_pad('(' . $layout . ')', 20) . $count . " times\n";
}

echo "\n\nCurrently Supported in Export:\n";
$supported = [
    'hero' => ['default', 'slideshow', 'slider', 'centered'],
    'page-header' => ['default'],
    'stats' => ['default'],
    'about' => ['default', 'image-left', 'image-right'],
    'services' => ['grid', 'list', 'cards-images'],
    'features' => ['grid'],
    'contact' => ['default'],
    'cta' => ['default', 'banner'],
    'testimonials' => ['default'],
    'gallery' => ['default', 'masonry'],
    'text' => ['default'],
];

foreach ($supported as $type => $layouts) {
    echo "- $type: " . implode(', ', $layouts) . "\n";
}

echo "\n\nMissing Support:\n";
$missing = [];
foreach ($sectionTypes as $key => $count) {
    list($type, $layout) = explode('|', $key);
    if (!isset($supported[$type]) || !in_array($layout, $supported[$type])) {
        $missing[] = "❌ $type ($layout) - used $count times";
    }
}

if (empty($missing)) {
    echo "✅ All section types are supported!\n";
} else {
    foreach ($missing as $item) {
        echo "$item\n";
    }
}

