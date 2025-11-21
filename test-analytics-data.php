<?php

/**
 * Test script to verify analytics data generation
 * Run with: php test-analytics-data.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\RecommendationEngine;
use App\Services\PredictiveAnalyticsService;

// Get a test user (change ID as needed)
$userId = 1; // Change this to your test user ID
$user = User::find($userId);

if (!$user) {
    echo "❌ User not found with ID: {$userId}\n";
    exit(1);
}

echo "Testing analytics for user: {$user->name} (ID: {$user->id})\n";
echo str_repeat('=', 60) . "\n\n";

// Test AnalyticsService
echo "1️⃣ Testing AnalyticsService::getMemberPerformance()\n";
echo str_repeat('-', 60) . "\n";
$analyticsService = new AnalyticsService();
$performance = $analyticsService->getMemberPerformance($user);
echo "✅ Performance data generated\n";
echo "   - Health Score: {$performance['health_score']}/100\n";
echo "   - Total Earnings: K" . number_format($performance['earnings']['total'], 2) . "\n";
echo "   - Network Size: {$performance['network']['total_size']}\n\n";

// Test RecommendationEngine
echo "2️⃣ Testing RecommendationEngine\n";
echo str_repeat('-', 60) . "\n";
$recommendationEngine = new RecommendationEngine();
$recommendations = $recommendationEngine->generateRecommendations($user);
echo "✅ Recommendations generated: " . count($recommendations) . "\n";
foreach ($recommendations as $rec) {
    echo "   - [{$rec['priority']}] {$rec['title']}\n";
}
if (empty($recommendations)) {
    echo "   ℹ️ No recommendations (user may be performing well)\n";
}
echo "\n";

// Test PredictiveAnalyticsService - Growth Potential
echo "3️⃣ Testing PredictiveAnalyticsService::calculateGrowthPotential()\n";
echo str_repeat('-', 60) . "\n";
$predictiveService = new PredictiveAnalyticsService();
$growthPotential = $predictiveService->calculateGrowthPotential($user);
echo "✅ Growth Potential calculated\n";
echo "   - Current Monthly: K" . number_format($growthPotential['current_monthly_potential'], 2) . "\n";
echo "   - Full Activation: K" . number_format($growthPotential['full_activation_potential'], 2) . "\n";
echo "   - Untapped: K" . number_format($growthPotential['untapped_potential'], 2) . "\n";
echo "   - Opportunities: " . count($growthPotential['growth_opportunities']) . "\n";
foreach ($growthPotential['growth_opportunities'] as $opp) {
    echo "     • {$opp['title']} (+{$opp['potential_increase']})\n";
}
echo "\n";

// Test PredictiveAnalyticsService - Next Milestone
echo "4️⃣ Testing PredictiveAnalyticsService::getNextMilestone()\n";
echo str_repeat('-', 60) . "\n";
$nextMilestone = $predictiveService->getNextMilestone($user);
if ($nextMilestone) {
    echo "✅ Next Milestone found\n";
    echo "   - Level: {$nextMilestone['milestone']['level']}\n";
    echo "   - Reward: {$nextMilestone['milestone']['reward']}\n";
    echo "   - Progress: {$nextMilestone['current_progress']}%\n";
    echo "   - Remaining: {$nextMilestone['remaining']} referrals\n";
    if ($nextMilestone['estimated_days']) {
        echo "   - Estimated: ~{$nextMilestone['estimated_days']} days\n";
    }
} else {
    echo "ℹ️ No next milestone (user may be at max level)\n";
}
echo "\n";

// Test complete API response
echo "5️⃣ Testing Complete API Response\n";
echo str_repeat('-', 60) . "\n";
$apiResponse = [
    'performance' => $performance,
    'recommendations' => $recommendationEngine->getActiveRecommendations($user),
    'growthPotential' => $growthPotential,
    'nextMilestone' => $nextMilestone,
];
echo "✅ Complete API response structure:\n";
echo "   - performance: " . (isset($apiResponse['performance']) ? '✓' : '✗') . "\n";
echo "   - recommendations: " . count($apiResponse['recommendations']) . " items\n";
echo "   - growthPotential: " . (isset($apiResponse['growthPotential']) ? '✓' : '✗') . "\n";
echo "   - nextMilestone: " . (isset($apiResponse['nextMilestone']) && $apiResponse['nextMilestone'] ? '✓' : '✗') . "\n";
echo "\n";

echo str_repeat('=', 60) . "\n";
echo "✅ All tests completed successfully!\n";
echo "\nJSON Response Preview:\n";
echo json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
