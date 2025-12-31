<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Get first user
$user = \App\Models\User::first();
echo "User: {$user->name} (ID: {$user->id})\n";

// Get the service
$service = app(\App\Services\GrowBuilder\AIUsageService::class);

// Check current count
$before = DB::table('growbuilder_ai_usage')->count();
echo "Records before: {$before}\n";

// Record usage
try {
    $result = $service->recordUsage($user, 'test', 'test prompt', 0, null, 'groq');
    echo "recordUsage returned: " . ($result ? 'true' : 'false') . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Check count after
$after = DB::table('growbuilder_ai_usage')->count();
echo "Records after: {$after}\n";

// Get usage stats
$stats = $service->getUsageStats($user);
echo "Usage stats: " . json_encode($stats) . "\n";
