<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\RecommendationEngine;
use App\Services\PredictiveAnalyticsService;

// Get a test user (change ID as needed)
$userId = $argv[1] ?? 1;
$user = User::find($userId);

if (!$user) {
    echo "❌ User not found with ID: $userId\n";
    exit(1);
}

echo "🔍 Testing Analytics Data for User: {$user->name} (ID: {$user->id})\n";
echo str_repeat('=', 70) . "\n\n";

// Initialize services
$analyticsService = new AnalyticsService();
$recommendationEngine = new RecommendationEngine();
$predictiveService = new PredictiveAnalyticsService();

// Test Performance Data
echo "📊 PERFORMANCE DATA:\n";
echo str_repeat('-', 70) . "\n";
try {
    $performance = $analyticsService->getMemberPerformance($user);
    echo "✅ Performance data retrieved\n";
    echo "   - Health Score: " . ($performance['health_score'] ?? 'N/A') . "\n";
    echo "   - Total Earnings: K" . ($performance['earnings']['total'] ?? 0) . "\n";
    echo "   - Network Size: " . ($performance['network']['total_size'] ?? 0) . "\n";
    echo json_encode($performance, JSON_PRETTY_PRINT) . "\n\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// Test Recommendations
echo "💡 RECOMMENDATIONS:\n";
echo str_repeat('-', 70) . "\n";
try {
    $recommendationEngine->generateRecommendations($user);
    $recommendations = $recommendationEngine->getActiveRecommendations($user);
    echo "✅ Recommendations retrieved: " . count($recommendations) . " items\n";
    if (empty($recommendations)) {
        echo "⚠️  No recommendations available\n";
    } else {
        foreach ($recommendations as $rec) {
            echo "   - " . ($rec['title'] ?? 'Untitled') . "\n";
        }
    }
    echo json_encode($recommendations, JSON_PRETTY_PRINT) . "\n\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// Test Growth Potential
echo "📈 GROWTH POTENTIAL:\n";
echo str_repeat('-', 70) . "\n";
try {
    $growthPotential = $predictiveService->calculateGrowthPotential($user);
    if ($growthPotential) {
        echo "✅ Growth potential calculated\n";
        echo "   - Current Monthly: K" . ($growthPotential['current_monthly_potential'] ?? 0) . "\n";
        echo "   - Full Activation: K" . ($growthPotential['full_activation_potential'] ?? 0) . "\n";
        echo "   - Untapped: K" . ($growthPotential['untapped_potential'] ?? 0) . "\n";
        echo json_encode($growthPotential, JSON_PRETTY_PRINT) . "\n\n";
    } else {
        echo "⚠️  Growth potential is NULL\n\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// Test Next Milestone
echo "🎯 NEXT MILESTONE:\n";
echo str_repeat('-', 70) . "\n";
try {
    $nextMilestone = $predictiveService->getNextMilestone($user);
    if ($nextMilestone) {
        echo "✅ Next milestone found\n";
        echo "   - Level: " . ($nextMilestone['milestone']['level'] ?? 'N/A') . "\n";
        echo "   - Remaining: " . ($nextMilestone['remaining'] ?? 0) . " referrals\n";
        echo "   - Progress: " . ($nextMilestone['current_progress'] ?? 0) . "%\n";
        echo json_encode($nextMilestone, JSON_PRETTY_PRINT) . "\n\n";
    } else {
        echo "⚠️  Next milestone is NULL (user may have reached max level)\n\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// Test Complete API Response
echo "🌐 COMPLETE API RESPONSE:\n";
echo str_repeat('-', 70) . "\n";
try {
    $response = [
        'performance' => $analyticsService->getMemberPerformance($user),
        'recommendations' => $recommendationEngine->getActiveRecommendations($user),
        'growthPotential' => $predictiveService->calculateGrowthPotential($user),
        'nextMilestone' => $predictiveService->getNextMilestone($user),
    ];
    
    echo "✅ Complete response structure:\n";
    echo "   - Performance: " . (isset($response['performance']) ? '✓' : '✗') . "\n";
    echo "   - Recommendations: " . (isset($response['recommendations']) ? count($response['recommendations']) . ' items' : '✗') . "\n";
    echo "   - Growth Potential: " . (isset($response['growthPotential']) && $response['growthPotential'] ? '✓' : '✗') . "\n";
    echo "   - Next Milestone: " . (isset($response['nextMilestone']) && $response['nextMilestone'] ? '✓' : '✗') . "\n\n";
    
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('=', 70) . "\n";
echo "✅ Test complete!\n";
