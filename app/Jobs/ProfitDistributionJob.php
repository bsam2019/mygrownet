<?php

namespace App\Jobs;

use App\Domain\Financial\Services\ProfitDistributionService;
use App\Models\User;
use App\Notifications\ProfitDistributionNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Exception;

class ProfitDistributionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300; // 5 minutes
    public $backoff = [60, 120, 300]; // Retry after 1, 2, and 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $distributionType,
        public float $totalProfit,
        public Carbon $distributionDate,
        public int $createdBy,
        public ?float $bonusPoolPercentage = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ProfitDistributionService $profitDistributionService): void
    {
        try {
            Log::info("Starting {$this->distributionType} profit distribution", [
                'total_profit' => $this->totalProfit,
                'distribution_date' => $this->distributionDate->toDateString(),
                'created_by' => $this->createdBy,
                'bonus_pool_percentage' => $this->bonusPoolPercentage
            ]);

            $result = match ($this->distributionType) {
                'annual' => $this->processAnnualDistribution($profitDistributionService),
                'quarterly' => $this->processQuarterlyDistribution($profitDistributionService),
                default => throw new Exception("Invalid distribution type: {$this->distributionType}")
            };

            if ($result['success']) {
                $this->sendSuccessNotifications($result);
                Log::info("Successfully completed {$this->distributionType} profit distribution", $result);
            } else {
                throw new Exception($result['error'] ?? 'Unknown error occurred during distribution');
            }

        } catch (Exception $e) {
            Log::error("Failed to process {$this->distributionType} profit distribution", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'total_profit' => $this->totalProfit,
                'distribution_date' => $this->distributionDate->toDateString()
            ]);

            $this->sendFailureNotifications($e);
            throw $e;
        }
    }

    /**
     * Process annual profit distribution
     */
    protected function processAnnualDistribution(ProfitDistributionService $service): array
    {
        return $service->processAnnualProfitDistribution(
            $this->totalProfit,
            $this->distributionDate,
            $this->createdBy
        );
    }

    /**
     * Process quarterly bonus distribution
     */
    protected function processQuarterlyDistribution(ProfitDistributionService $service): array
    {
        $bonusPercentage = $this->bonusPoolPercentage ?? 7.5;
        
        return $service->processQuarterlyBonusDistribution(
            $this->totalProfit,
            $bonusPercentage,
            $this->distributionDate,
            $this->createdBy
        );
    }

    /**
     * Send success notifications to users and administrators
     */
    protected function sendSuccessNotifications(array $result): void
    {
        try {
            // Notify the creator/administrator
            $creator = User::find($this->createdBy);
            if ($creator) {
                $creator->notify(new ProfitDistributionNotification([
                    'type' => 'admin_success',
                    'distribution_type' => $this->distributionType,
                    'total_distributed' => $result['total_distributed'],
                    'user_count' => $result['user_count'],
                    'distribution_date' => $this->distributionDate,
                    'distribution_id' => $result['distribution_id']
                ]));
            }

            // Notify affected users about their profit distribution
            if (isset($result['processed_shares'])) {
                $this->notifyUsersAboutDistribution($result['processed_shares']);
            } elseif (isset($result['processed_bonuses'])) {
                $this->notifyUsersAboutDistribution($result['processed_bonuses']);
            }

        } catch (Exception $e) {
            Log::warning("Failed to send success notifications for profit distribution", [
                'error' => $e->getMessage(),
                'distribution_type' => $this->distributionType
            ]);
        }
    }

    /**
     * Notify users about their profit distribution
     */
    protected function notifyUsersAboutDistribution(array $distributions): void
    {
        foreach ($distributions as $distribution) {
            try {
                $user = User::find($distribution['user_id']);
                if ($user) {
                    $user->notify(new ProfitDistributionNotification([
                        'type' => 'user_distribution',
                        'distribution_type' => $this->distributionType,
                        'amount' => $distribution['amount'],
                        'distribution_date' => $this->distributionDate,
                        'profit_share_id' => $distribution['profit_share_id'] ?? null
                    ]));
                }
            } catch (Exception $e) {
                Log::warning("Failed to notify user about profit distribution", [
                    'user_id' => $distribution['user_id'],
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Send failure notifications to administrators
     */
    protected function sendFailureNotifications(Exception $exception): void
    {
        try {
            $creator = User::find($this->createdBy);
            if ($creator) {
                $creator->notify(new ProfitDistributionNotification([
                    'type' => 'admin_failure',
                    'distribution_type' => $this->distributionType,
                    'error_message' => $exception->getMessage(),
                    'distribution_date' => $this->distributionDate,
                    'total_profit' => $this->totalProfit
                ]));
            }

            // Notify other administrators
            $admins = User::role('admin')->where('id', '!=', $this->createdBy)->get();
            Notification::send($admins, new ProfitDistributionNotification([
                'type' => 'admin_failure',
                'distribution_type' => $this->distributionType,
                'error_message' => $exception->getMessage(),
                'distribution_date' => $this->distributionDate,
                'total_profit' => $this->totalProfit,
                'created_by' => $this->createdBy
            ]));

        } catch (Exception $e) {
            Log::error("Failed to send failure notifications for profit distribution", [
                'original_error' => $exception->getMessage(),
                'notification_error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle job failure
     */
    public function failed(Exception $exception): void
    {
        Log::critical("ProfitDistributionJob failed permanently", [
            'distribution_type' => $this->distributionType,
            'total_profit' => $this->totalProfit,
            'distribution_date' => $this->distributionDate->toDateString(),
            'created_by' => $this->createdBy,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // Send critical failure notification
        try {
            $admins = User::role('admin')->get();
            Notification::send($admins, new ProfitDistributionNotification([
                'type' => 'critical_failure',
                'distribution_type' => $this->distributionType,
                'error_message' => $exception->getMessage(),
                'distribution_date' => $this->distributionDate,
                'total_profit' => $this->totalProfit,
                'attempts' => $this->attempts()
            ]));
        } catch (Exception $e) {
            Log::error("Failed to send critical failure notification", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'profit-distribution',
            $this->distributionType,
            "user:{$this->createdBy}",
            "date:{$this->distributionDate->format('Y-m-d')}"
        ];
    }
}