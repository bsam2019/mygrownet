<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Simulate authenticated user (Jason)
$jason = App\Models\User::where('name', 'like', '%Jason%')->first();

if (!$jason) {
    echo "Jason not found\n";
    exit;
}

// Authenticate as Jason
Auth::login($jason);

echo "Simulating ReferralController@index for: {$jason->name}\n";
echo "========================================================\n\n";

// Create controller instance
$referralService = app(App\Services\ReferralService::class);
$controller = new App\Http\Controllers\ReferralController($referralService);

// Call the index method
$response = $controller->index();

// Get the props that would be sent to Vue
$props = $response->props;

echo "teamMembers prop:\n";
echo "-----------------\n";
if (isset($props['teamMembers'])) {
    foreach ($props['teamMembers'] as $member) {
        echo "Name: {$member['name']}\n";
        echo "  is_active: " . ($member['is_active'] ? 'TRUE' : 'FALSE') . "\n";
        echo "  has_starter_kit: " . ($member['has_starter_kit'] ? 'TRUE' : 'FALSE') . "\n";
        echo "  status: {$member['status']}\n";
        echo "\n";
    }
} else {
    echo "teamMembers prop not found!\n";
}

echo "\nlevel1Referrals prop:\n";
echo "---------------------\n";
if (isset($props['level1Referrals'])) {
    foreach ($props['level1Referrals'] as $member) {
        echo "Name: {$member['name']}\n";
        echo "  is_active: " . ($member['is_active'] ? 'TRUE' : 'FALSE') . "\n";
        echo "  has_starter_kit: " . ($member['has_starter_kit'] ? 'TRUE' : 'FALSE') . "\n";
        echo "  status: {$member['status']}\n";
        echo "\n";
    }
} else {
    echo "level1Referrals prop not found!\n";
}
