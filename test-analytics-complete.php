<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test with a real user
$userId = $argv[1] ?? 1;
$user = \App\Models\User::find($userId);

if (!$user) {
    echo "❌ User not found\n";
    exit(1);
}

echo "Testing Analytics for: {$user->name} (ID: {$user->id})\n";
echo str_repeat('=', 60) . "\n\n";

try {
    // Test AnalyticsService
    echo "1️⃣  Testing AnalyticsService...\n";
    $analyticsService = app(\App\Services\AnalyticsService::class);
    $performance = $analyticsService->getMemberPerformance($user);
    echo "✅ Performance data generated\n";
    echo "   - Total Earnings: K" . number_format($performance['earnings']['total'], 2) . "\n";
    echo "   - Network Size: " . $performance['network']['total_size'] . "\n";
    echo "   - Health Score: " . $performance['health_score'] . "/100\n\n";
    
    // Test RecommendationEngine
    echo "2️⃣  Testing RecommendationEngine...\n";
    $recommendationEngine = app(\App\Services\RecommendationEngine::class);
    $recommendationEngine->generateRecommendations($user);
    $recommendations = $recommendationEngine->getActiveRecommendations($user);
    echo "✅ Recommendations generated: " . count($recommendations) . " recommendations\n";
    if (count($recommendations) > 0) {
        foreach ($recommendations as $rec) {
            echo "   - [{$rec['priority']}] {$rec['title']}\n";
        }
    } else {
        echo "   ⚠️  No recommendations generated\n";
    }
    echo "\n";
    
    // Test PredictiveAnalyticsService - Growth Potential
    echo "3️⃣  Testing Growth Potential...\n";
    $predictiveService = app(\App\Services\PredictiveAnalyticsService::class);
    $growthPotential = $predictiveService->calculateGrowthPotential($user);
    echo "✅ Growth Potential calculated\n";
    echo "   - Current Monthly: K" . number_format($growthPotential['current_monthly_potential'] ?? 0, 2) . "\n";
    echo "   - Full Activation: K" . number_format($growthPotential['full_activation_potential'] ?? 0, 2) . "\n";
    echo "   - Untapped: K" . number_format($growthPotential['untapped_potential'] ?? 0, 2) . "\n";
    echo "   - Opportunities: " . count($growthPotential['growth_opportunities'] ?? []) . "\n\n";
    
    // Test PredictiveAnalyticsService - Next Milestone
    echo "4️⃣  Testing Next Milestone...\n";
    $nextMilestone = $predictiveService->getNextMilestone($user);
    if ($nextMilestone) {
        echo "✅ Next Milestone found\n";
        echo "   - Level: " . ($nextMilestone['milestone']['level'] ?? 'N/A') . "\n";
        echo "   - Reward: " . ($nextMilestone['milestone']['reward'] ?? 'N/A') . "\n";
        echo "   - Remaining: " . ($nextMilestone['remaining'] ?? 0) . " referrals\n";
        echo "   - Progress: " . ($nextMilestone['current_progress'] ?? 0) . "%\n";
    } else {
        echo "⚠️  No milestone data (user may be at max level)\n";
    }
    echo "\n";
    
    // Test full API response
    echo "5️⃣  Testing Full API Response...\n";
    $fullResponse = [
        'performance' => $performance,
        'recommendations' => $recommendations,
        'growthPotential' => $growthPotential,
        'nextMilestone' => $nextMilestone,
    ];
    echo "✅ Full response structure ready\n";
    echo "   - Performance: " . (isset($fullResponse['performance']) ? '✓' : '✗') . "\n";
    echo "   - Recommendations: " . (isset($fullResponse['recommendations']) ? '✓' : '✗') . " (" . count($fullResponse['recommendations']) . ")\n";
    echo "   - Growth Potential: " . (isset($fullResponse['growthPotential']) ? '✓' : '✗') . "\n";
    echo "   - Next Milestone: " . (isset($fullResponse['nextMilestone']) ? '✓' : '✗') . "\n\n";
    
    echo str_repeat('=', 60) . "\n";
    echo "✅ ALL TESTS PASSED\n";
    echo "\nJSON Response Preview:\n";
    echo json_encode($fullResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
