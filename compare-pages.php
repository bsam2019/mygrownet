<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$siteId = 15;

// Get all pages
$pages = App\Infrastructure\GrowBuilder\Models\GrowBuilderPage::where('site_id', $siteId)
    ->orderBy('id')
    ->get();

$working = ['home', 'about', 'contact'];
$notWorking = ['services', 'ministries', 'visit', 'school'];

echo "Comparing working vs non-working pages\n\n";

echo "WORKING PAGES:\n";
foreach ($pages as $page) {
    if (in_array($page->slug, $working)) {
        echo "\n{$page->title} ({$page->slug}):\n";
        echo "  ID: {$page->id}\n";
        echo "  Slug: {$page->slug}\n";
        echo "  Published: " . ($page->is_published ? 'yes' : 'no') . "\n";
        echo "  Homepage: " . ($page->is_homepage ? 'yes' : 'no') . "\n";
        echo "  Show in nav: " . ($page->show_in_nav ? 'yes' : 'no') . "\n";
        echo "  Nav order: {$page->nav_order}\n";
        echo "  Content sections: " . count($page->content_json['sections'] ?? $page->content_json ?? []) . "\n";
        echo "  Created: {$page->created_at}\n";
        echo "  Updated: {$page->updated_at}\n";
    }
}

echo "\n\nNOT WORKING PAGES:\n";
foreach ($pages as $page) {
    if (in_array($page->slug, $notWorking)) {
        echo "\n{$page->title} ({$page->slug}):\n";
        echo "  ID: {$page->id}\n";
        echo "  Slug: {$page->slug}\n";
        echo "  Published: " . ($page->is_published ? 'yes' : 'no') . "\n";
        echo "  Homepage: " . ($page->is_homepage ? 'yes' : 'no') . "\n";
        echo "  Show in nav: " . ($page->show_in_nav ? 'yes' : 'no') . "\n";
        echo "  Nav order: {$page->nav_order}\n";
        echo "  Content sections: " . count($page->content_json['sections'] ?? $page->content_json ?? []) . "\n";
        echo "  Created: {$page->created_at}\n";
        echo "  Updated: {$page->updated_at}\n";
    }
}

// Check if there's a pattern in the content structure
echo "\n\nCONTENT STRUCTURE COMPARISON:\n";
$workingPage = $pages->where('slug', 'about')->first();
$notWorkingPage = $pages->where('slug', 'services')->first();

echo "\nWorking page (about) content keys:\n";
print_r(array_keys($workingPage->content_json));

echo "\nNot working page (services) content keys:\n";
print_r(array_keys($notWorkingPage->content_json));
