<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Simulate authenticated request
$userId = $argv[1] ?? 5;
$user = \App\Models\User::find($userId);

if (!$user) {
    echo "❌ User not found\n";
    exit(1);
}

echo "Testing Analytics API for: {$user->name} (ID: {$user->id})\n";
echo str_repeat('=', 60) . "\n\n";

// Manually call the controller method
try {
    $controller = new \App\Http\Controllers\MyGrowNet\AnalyticsController(
        app(\App\Services\AnalyticsService::class),
        app(\App\Services\RecommendationEngine::class),
        app(\App\Services\PredictiveAnalyticsService::class)
    );
    
    // Create a mock request
    $request = \Illuminate\Http\Request::create('/analytics/performance', 'GET');
    $request->setUserResolver(function() use ($user) {
        return $user;
    });
    
    echo "Calling AnalyticsController::performance()...\n";
    $response = $controller->performance($request);
    
    echo "✅ Response Status: " . $response->getStatusCode() . "\n\n";
    
    $content = $response->getContent();
    $data = json_decode($content, true);
    
    if (isset($data['error'])) {
        echo "❌ API Error: " . $data['error'] . "\n";
        echo "Message: " . ($data['message'] ?? 'N/A') . "\n";
        exit(1);
    }
    
    echo "Response Structure:\n";
    echo "- performance: " . (isset($data['performance']) ? '✓' : '✗') . "\n";
    echo "- recommendations: " . (isset($data['recommendations']) ? '✓ (' . count($data['recommendations']) . ')' : '✗') . "\n";
    echo "- growthPotential: " . (isset($data['growthPotential']) ? '✓' : '✗') . "\n";
    echo "- nextMilestone: " . (isset($data['nextMilestone']) ? '✓' : '✗') . "\n\n";
    
    if (isset($data['recommendations']) && count($data['recommendations']) > 0) {
        echo "Recommendations:\n";
        foreach ($data['recommendations'] as $rec) {
            echo "  - [{$rec['priority']}] {$rec['title']}\n";
        }
        echo "\n";
    }
    
    if (isset($data['nextMilestone'])) {
        echo "Next Milestone:\n";
        echo "  - Level: " . ($data['nextMilestone']['milestone']['level'] ?? 'N/A') . "\n";
        echo "  - Progress: " . ($data['nextMilestone']['current_progress'] ?? 0) . "%\n";
        echo "  - Remaining: " . ($data['nextMilestone']['remaining'] ?? 0) . " referrals\n\n";
    }
    
    if (isset($data['growthPotential'])) {
        echo "Growth Potential:\n";
        echo "  - Current: K" . number_format($data['growthPotential']['current_monthly_potential'] ?? 0, 2) . "\n";
        echo "  - Untapped: K" . number_format($data['growthPotential']['untapped_potential'] ?? 0, 2) . "\n\n";
    }
    
    echo str_repeat('=', 60) . "\n";
    echo "✅ API TEST PASSED\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
