<?php

namespace App\Application\Services;

use App\Infrastructure\Persistence\Eloquent\EmailMarketing\EmailABTestModel;
use App\Infrastructure\Persistence\Eloquent\EmailMarketing\EmailTrackingModel;
use App\Infrastructure\Persistence\Eloquent\EmailMarketing\EmailQueueModel;
use Illuminate\Support\Facades\DB;

class ABTestService
{
    /**
     * Create a new A/B test
     */
    public function createTest(array $data): EmailABTestModel
    {
        return EmailABTestModel::create([
            'campaign_id' => $data['campaign_id'],
            'name' => $data['name'],
            'test_type' => $data['test_type'],
            'variant_a_id' => $data['variant_a_id'],
            'variant_b_id' => $data['variant_b_id'],
            'split_percentage' => $data['split_percentage'] ?? 50,
            'winner_metric' => $data['winner_metric'],
            'status' => 'draft',
        ]);
    }

    /**
     * Start an A/B test
     */
    public function startTest(int $testId): bool
    {
        $test = EmailABTestModel::findOrFail($testId);
        
        $test->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        return true;
    }

    /**
     * Determine which variant to send to a user
     */
    public function getVariantForUser(int $testId, int $userId): string
    {
        $test = EmailABTestModel::findOrFail($testId);
        
        // Use user ID to deterministically assign variant
        // This ensures same user always gets same variant
        $hash = crc32($testId . '-' . $userId);
        $percentage = $hash % 100;
        
        return $percentage < $test->split_percentage ? 'a' : 'b';
    }

    /**
     * Get template ID for a user based on A/B test
     */
    public function getTemplateForUser(int $testId, int $userId): int
    {
        $test = EmailABTestModel::findOrFail($testId);
        $variant = $this->getVariantForUser($testId, $userId);
        
        return $variant === 'a' ? $test->variant_a_id : $test->variant_b_id;
    }

    /**
     * Calculate test results
     */
    public function calculateResults(int $testId): array
    {
        $test = EmailABTestModel::with(['variantA', 'variantB'])->findOrFail($testId);
        
        // Get emails sent for each variant
        $variantAEmails = EmailQueueModel::where('template_id', $test->variant_a_id)
            ->where('campaign_id', $test->campaign_id)
            ->where('status', 'sent')
            ->pluck('id');
            
        $variantBEmails = EmailQueueModel::where('template_id', $test->variant_b_id)
            ->where('campaign_id', $test->campaign_id)
            ->where('status', 'sent')
            ->pluck('id');

        // Calculate metrics for variant A
        $variantAStats = $this->calculateVariantStats($variantAEmails);
        
        // Calculate metrics for variant B
        $variantBStats = $this->calculateVariantStats($variantBEmails);

        return [
            'test' => $test,
            'variant_a' => [
                'template' => $test->variantA,
                'stats' => $variantAStats,
            ],
            'variant_b' => [
                'template' => $test->variantB,
                'stats' => $variantBStats,
            ],
            'winner' => $this->determineWinner($test, $variantAStats, $variantBStats),
        ];
    }

    /**
     * Calculate stats for a variant
     */
    private function calculateVariantStats($emailIds): array
    {
        if ($emailIds->isEmpty()) {
            return [
                'sent' => 0,
                'opened' => 0,
                'clicked' => 0,
                'open_rate' => 0,
                'click_rate' => 0,
            ];
        }

        $sent = $emailIds->count();
        
        $opened = EmailTrackingModel::whereIn('queue_id', $emailIds)
            ->where('event_type', 'opened')
            ->distinct('queue_id')
            ->count();
            
        $clicked = EmailTrackingModel::whereIn('queue_id', $emailIds)
            ->where('event_type', 'clicked')
            ->distinct('queue_id')
            ->count();

        return [
            'sent' => $sent,
            'opened' => $opened,
            'clicked' => $clicked,
            'open_rate' => $sent > 0 ? round(($opened / $sent) * 100, 2) : 0,
            'click_rate' => $sent > 0 ? round(($clicked / $sent) * 100, 2) : 0,
        ];
    }

    /**
     * Determine the winner based on the metric
     */
    private function determineWinner(EmailABTestModel $test, array $variantAStats, array $variantBStats): ?string
    {
        $metric = $test->winner_metric;
        
        $aValue = match($metric) {
            'open_rate' => $variantAStats['open_rate'],
            'click_rate' => $variantAStats['click_rate'],
            default => 0,
        };
        
        $bValue = match($metric) {
            'open_rate' => $variantBStats['open_rate'],
            'click_rate' => $variantBStats['click_rate'],
            default => 0,
        };

        if ($aValue === $bValue) {
            return null;
        }

        return $aValue > $bValue ? 'a' : 'b';
    }

    /**
     * Declare a winner and complete the test
     */
    public function declareWinner(int $testId, ?string $winner = null): bool
    {
        $test = EmailABTestModel::findOrFail($testId);
        
        // Auto-calculate winner if not provided
        if ($winner === null) {
            $results = $this->calculateResults($testId);
            $winner = $results['winner'] ?? 'none';
        }

        $test->update([
            'winner_variant' => $winner,
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        return true;
    }

    /**
     * Get all tests for a campaign
     */
    public function getTestsForCampaign(int $campaignId): \Illuminate\Database\Eloquent\Collection
    {
        return EmailABTestModel::with(['variantA', 'variantB'])
            ->where('campaign_id', $campaignId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
<?php

namespace App\Application\Services;

use App\Infrastructure\Persistence\Eloquent\EmailMarketing\EmailABTestModel;
use App\Infrastructure\Persistence\Eloquent\EmailMarketing\EmailCampaignModel;
use App\Infrastructure\Persistence\Eloquent\EmailMarketing\E