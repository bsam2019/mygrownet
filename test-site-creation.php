<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Application\GrowBuilder\DTOs\CreateSiteDTO;
use App\Application\GrowBuilder\UseCases\CreateSiteUseCase;
use App\Application\GrowBuilder\UseCases\ApplySiteTemplateUseCase;
use App\Models\GrowBuilder\SiteTemplate;

$user = \App\Models\User::first();

if (!$user) {
    echo "No users found\n";
    exit;
}

echo "Testing site creation with template...\n";
echo "User: {$user->name}\n\n";

// Get a template
$template = SiteTemplate::first();

if (!$template) {
    echo "No templates found\n";
    exit;
}

echo "Template: {$template->name}\n";
echo "Template has " . $template->pages->count() . " pages\n\n";

try {
    // Create site
    $createUseCase = app(CreateSiteUseCase::class);
    $dto = new CreateSiteDTO(
        userId: $user->id,
        name: 'Test Site ' . time(),
        subdomain: 'test-' . time(),
        templateId: null,
        description: 'Test site creation'
    );
    
    $site = $createUseCase->execute($dto);
    echo "✅ Site created: {$site->getName()}\n";
    echo "Site ID: {$site->getId()->value()}\n\n";
    
    // Apply template
    echo "Applying template...\n";
    $applyTemplate = app(ApplySiteTemplateUseCase::class);
    $applyTemplate->execute($site->getId()->value(), $template->id);
    
    echo "✅ Template applied successfully\n\n";
    
    // Check pages
    $pages = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPage::where('site_id', $site->getId()->value())->get();
    echo "Pages created: " . $pages->count() . "\n";
    foreach ($pages as $page) {
        echo "  - {$page->title} ({$page->slug})\n";
        echo "    Has content: " . (isset($page->content_json) && !empty($page->content_json) ? 'Yes' : 'No') . "\n";
        if (isset($page->content_json['sections'])) {
            echo "    Sections: " . count($page->content_json['sections']) . "\n";
        }
    }
    
    // Check navigation links
    $siteModel = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::find($site->getId()->value());
    echo "\nNavigation Links:\n";
    if (isset($siteModel->settings['navigation']['links'])) {
        foreach ($siteModel->settings['navigation']['links'] as $link) {
            echo "  - {$link['label']} → {$link['url']}\n";
        }
    } else {
        echo "  No navigation links found\n";
    }
    
    // Clean up
    echo "\nCleaning up...\n";
    \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::find($site->getId()->value())->delete();
    echo "✅ Test site deleted\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
