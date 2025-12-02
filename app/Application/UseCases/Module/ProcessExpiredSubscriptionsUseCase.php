<?php

namespace App\Application\UseCases\Module;

use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\Module\Services\ModuleSubscriptionService;
use Illuminate\Support\Facades\Log;

class ProcessExpiredSubscriptionsUseCase
{
    public function __construct(
        private ModuleSubscriptionRepositoryInterface $subscriptionRepository,
        private ModuleSubscriptionService $subscriptionService
    ) {}

    /**
     * Process all expired subscriptions
     * This should be run as a scheduled task
     *
     * @return array Statistics about processed subscriptions
     */
    public function execute(): array
    {
        $stats = [
            'total_checked' => 0,
            'expired' => 0,
            'renewed' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        // Get all active subscriptions that are past their end date
        $expiredSubscriptions = $this->subscriptionRepository->findExpired();
        $stats['total_checked'] = count($expiredSubscriptions);

        foreach ($expiredSubscriptions as $subscription) {
            try {
                if ($subscription->isAutoRenew()) {
                    // Attempt to renew
                    try {
                        $this->subscriptionService->renew($subscription);
                        $this->subscriptionRepository->save($subscription);
                        $stats['renewed']++;
                        
                        Log::info("Subscription renewed", [
                            'subscription_id' => $subscription->getId(),
                            'user_id' => $subscription->getUserId(),
                            'module_id' => $subscription->getModuleId(),
                        ]);
                    } catch (\Exception $e) {
                        // If renewal fails, expire the subscription
                        $this->subscriptionService->expire($subscription);
                        $this->subscriptionRepository->save($subscription);
                        $stats['expired']++;
                        
                        Log::warning("Subscription renewal failed, expired", [
                            'subscription_id' => $subscription->getId(),
                            'error' => $e->getMessage(),
                        ]);
                    }
                } else {
                    // No auto-renew, just expire
                    $this->subscriptionService->expire($subscription);
                    $this->subscriptionRepository->save($subscription);
                    $stats['expired']++;
                    
                    Log::info("Subscription expired", [
                        'subscription_id' => $subscription->getId(),
                        'user_id' => $subscription->getUserId(),
                        'module_id' => $subscription->getModuleId(),
                    ]);
                }
            } catch (\Exception $e) {
                $stats['failed']++;
                $stats['errors'][] = [
                    'subscription_id' => $subscription->getId(),
                    'error' => $e->getMessage(),
                ];
                
                Log::error("Failed to process expired subscription", [
                    'subscription_id' => $subscription->getId(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return $stats;
    }
}
