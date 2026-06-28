<?php

namespace App\Helpers;

use App\Facades\QuickInvoice;
use App\Models\User;

/**
 * Helper class for Quick Invoice integration examples
 * 
 * This shows how other parts of the MyGrowNet platform
 * can integrate with the Quick Invoice system.
 */
class QuickInvoiceIntegration
{
    /**
     * Example: Create invoice for a project completion
     */
    public static function createProjectInvoice(User $user, array $projectData): array
    {
        return QuickInvoice::createDocument($user, [
            'document_type' => 'invoice',
            'client_name' => $projectData['client_name'],
            'client_email' => $projectData['client_email'] ?? null,
            'client_address' => $projectData['client_address'] ?? null,
            'items' => [
                [
                    'description' => "Project: {$projectData['project_name']}",
                    'quantity' => 1,
                    'rate' => $projectData['project_amount'],
                ],
            ],
            'template' => 'professional',
            'notes' => "Project completed on " . now()->format('M j, Y'),
            'integration_source' => 'project_management',
        ]);
    }
    
    /**
     * Example: Create service receipt
     */
    public static function createServiceReceipt(User $user, array $serviceData): array
    {
        $items = [];
        foreach ($serviceData['services'] as $service) {
            $items[] = [
                'description' => $service['name'],
                'quantity' => $service['hours'] ?? 1,
                'rate' => $service['rate'],
            ];
        }
        
        return QuickInvoice::createDocument($user, [
            'document_type' => 'receipt',
            'client_name' => $serviceData['client_name'],
            'client_phone' => $serviceData['client_phone'] ?? null,
            'items' => $items,
            'template' => 'service',
            'notes' => 'Payment received with thanks',
            'integration_source' => 'service_booking',
        ]);
    }
    
    /**
     * Example: Create product quotation
     */
    public static function createProductQuotation(User $user, array $quoteData): array
    {
        $items = [];
        foreach ($quoteData['products'] as $product) {
            $items[] = [
                'description' => $product['name'] . ' - ' . $product['description'],
                'quantity' => $product['quantity'],
                'rate' => $product['unit_price'],
            ];
        }
        
        return QuickInvoice::createDocument($user, [
            'document_type' => 'quotation',
            'client_name' => $quoteData['client_name'],
            'client_email' => $quoteData['client_email'],
            'items' => $items,
            'template' => 'retail',
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'notes' => 'Quote valid for 30 days',
            'integration_source' => 'product_catalog',
        ]);
    }
    
    /**
     * Check if user can create documents before showing invoice options
     */
    public static function canUserCreateInvoice(User $user): bool
    {
        $status = QuickInvoice::getUserSubscriptionStatus($user);
        return $status['can_create_document'];
    }
    
    /**
     * Get user's invoice creation status for UI display
     */
    public static function getUserInvoiceStatus(User $user): array
    {
        $status = QuickInvoice::getUserSubscriptionStatus($user);
        
        return [
            'can_create' => $status['can_create_document'],
            'remaining' => $status['remaining_documents'],
            'tier' => $status['tier_name'],
            'usage_percentage' => $status['usage_percentage'],
            'upgrade_needed' => $status['usage_percentage'] >= 80,
        ];
    }
}