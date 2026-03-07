<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Domain\GrowBuilder\Repositories\PageRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\SiteId;

$repo = app(PageRepositoryInterface::class);
$siteId = SiteId::fromInt(12);

echo "Testing page lookup for site ID 12:\n\n";

$slugs = ['services', 'ministries', 'visit', 'about', 'contact'];

foreach ($slugs as $slug) {
    echo "Slug: {$slug}\n";
    $page = $repo->findBySiteIdAndSlug($siteId, $slug);
    if ($page) {
        echo "  ✅ Found - ID: " . $page->getId()->value() . ", Published: " . ($page->isPublished() ? 'yes' : 'no') . "\n";
    } else {
        echo "  ❌ Not found\n";
    }
}

echo "\nAll published pages:\n";
$pages = $repo->findPublishedBySiteId($siteId);
foreach ($pages as $page) {
    echo "  - {$page->getTitle()} (slug: {$page->getSlug()})\n";
}
