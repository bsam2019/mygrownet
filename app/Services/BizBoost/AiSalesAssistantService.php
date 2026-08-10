<?php

namespace App\Services\BizBoost;

use App\Domain\Core\Contracts\AiProviderInterface;
use App\Domain\Core\Contracts\AiGenerationResult;
use Illuminate\Support\Facades\DB;

class AiSalesAssistantService
{
    public function __construct(
        protected ?AiProviderInterface $aiProvider = null
    ) {}

    /**
     * Generate an instant 2-sentence conversational summary of a customer.
     */
    public function generateCustomerSummary(int $customerId): string
    {
        $customer = DB::table('bizboost_customers')->where('id', $customerId)->first();
        if (!$customer) {
            return "No customer record found.";
        }

        $eventsCount = DB::table('bizboost_analytics_events')->where('customer_id', $customerId)->count();

        if ($this->aiProvider) {
            $prompt = "Generate a concise 2-sentence sales summary for customer '{$customer->name}', source '{$customer->source}', total spent ZMW {$customer->total_spent}, with {$eventsCount} website interactions.";
            $result = $this->aiProvider->generateText($prompt);
            if ($result->success) {
                return $result->content;
            }
        }

        // Fallback rule-based summary
        $spentText = $customer->total_spent > 0 ? "Total spent ZMW {$customer->total_spent} across {$customer->total_orders} orders." : "No orders completed yet.";
        return "Customer {$customer->name} acquired via {$customer->source}. {$spentText}";
    }

    /**
     * Generate a personalized follow-up SMS or WhatsApp message draft.
     */
    public function draftFollowUpMessage(string $customerName, string $intentTier, string $productName): string
    {
        if ($this->aiProvider) {
            $prompt = "Draft a professional 160-character WhatsApp message for customer {$customerName} interested in {$productName}. Intent level: {$intentTier}. Include call to action.";
            $result = $this->aiProvider->generateText($prompt);
            if ($result->success) {
                return $result->content;
            }
        }

        return "Hi {$customerName}, thanks for your interest in {$productName}! Would you be available for a quick 5-minute chat today?";
    }
}
