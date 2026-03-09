<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$site = App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::find(15);

if (!$site) {
    echo "Site not found\n";
    exit(1);
}

echo "Site: {$site->name}\n";
echo "Subdomain: {$site->subdomain}\n";
echo "Custom Domain: {$site->custom_domain}\n";
echo "Status: {$site->status}\n";
echo "\nPages:\n";

$pages = $site->pages()->orderBy('nav_order')->get();

foreach ($pages as $page) {
    echo sprintf(
        "  - %-20s | slug: %-15s | published: %-3s | homepage: %-3s | show_in_nav: %-3s\n",
        $page->title,
        $page->slug,
        $page->is_published ? 'yes' : 'no',
        $page->is_homepage ? 'yes' : 'no',
        $page->show_in_nav ? 'yes' : 'no'
    );
}

echo "\nTotal pages: " . $pages->count() . "\n";
