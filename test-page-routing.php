<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$siteId = 15;
$slugs = ['home', 'about', 'services', 'ministries', 'visit', 'school', 'contact'];

echo "Testing page routing for site ID: {$siteId}\n\n";

foreach ($slugs as $slug) {
    echo "Testing slug: {$slug}\n";
    
    // Test direct database query
    $page = App\Infrastructure\GrowBuilder\Models\GrowBuilderPage::where('site_id', $siteId)
        ->where('slug', $slug)
        ->first();
    
    if ($page) {
        echo "  ✓ Found in database (ID: {$page->id}, published: " . ($page->is_published ? 'yes' : 'no') . ")\n";
    } else {
        echo "  ✗ NOT found in database\n";
    }
    
    // Test through repository
    try {
        $repo = app(App\Domain\GrowBuilder\Repositories\PageRepositoryInterface::class);
        $siteIdVO = App\Domain\GrowBuilder\ValueObjects\SiteId::fromInt($siteId);
        $domainPage = $repo->findBySiteIdAndSlug($siteIdVO, $slug);
        
        if ($domainPage) {
            echo "  ✓ Found through repository\n";
        } else {
            echo "  ✗ NOT found through repository\n";
        }
    } catch (\Exception $e) {
        echo "  ✗ Repository error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}
