<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking Starter Kit Content\n";
echo "============================\n\n";

$contentItems = App\Infrastructure\Persistence\Eloquent\StarterKit\ContentItemModel::all();

echo "Total Content Items: " . $contentItems->count() . "\n\n";

foreach ($contentItems as $item) {
    echo "- {$item->title}\n";
    echo "  Type: {$item->type}\n";
    echo "  Category: {$item->category}\n";
    echo "  Order: {$item->order}\n\n";
}

echo "\nLibrary Resources\n";
echo "=================\n\n";

$resources = App\Infrastructure\Persistence\Eloquent\Library\LibraryResourceModel::all();

echo "Total Resources: " . $resources->count() . "\n\n";

foreach ($resources as $resource) {
    echo "- {$resource->title}\n";
    echo "  Type: {$resource->type}\n";
    echo "  Category: {$resource->category}\n\n";
}
