<?php

namespace App\Services\QuickInvoice;

use App\Models\QuickInvoice\UserSubscription;
use App\Models\QuickInvoice\UsageTracking;
use App\Models\QuickInvoiceDocument;
use App\Models\QuickInvoiceProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Quick Invoice Service for internal CMS integration
 * 
 * This service allows other parts of the MyGrowNet platform to create
 * invoices, quotations, receipts, and delivery notes programmatically.
 */
class QuickInvoiceService
{
    /**
     * Create a document for a user
     */
    public function createDocument(User $user, array $data): array
    {
        // Check usage limits
        $subscription = UserSubscription::getOrCreateFreeSubscription($user->id);
        if (!$subscription->canCreateDocument()) {
            return [
                'success' => false,
                'error' => 'Usage limit exceeded',
                'message' => 'User has reached their monthly document limit.',
                'remaining_documents' => 0,
            ];
        }
        
        // Validate required fields
        $requiredFields = ['document_type', 'client_name', 'items'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return [
                    'success' => false,
                    'error' => "Missing required field: {$field}",
                ];
            }
        }
        
        // Validate document type
        $validTypes = ['invoice', 'quotation', 'receipt', 'delivery_note'];
        if (!in_array($data['document_type'], $validTypes)) {
            return [
                'success' => false,
                'error' => 'Invalid document type',
            ];
        }
        
        // Validate items
        if (!is_array($data['items']) || empty($data['items'])) {
            return [
                'success' => false,
                'error' => 'Items must be a non-empty array',
            ];
        }
        
        DB::beginTransaction();
        
        try {
            // Create document
            $document = new QuickInvoiceDocument();
            $document->user_id = $user->id;
            $document->document_type = $data['document_type'];
            $document->client_name = $data['client_name'];
            $document->client_email = $data['client_email'] ?? null;
            $document->client_phone = $data['client_phone'] ?? null;
            $document->client_address = $data['client_address'] ?? null;
            $document->currency = $data['currency'] ?? 'ZMW';
            $document->notes = $data['notes'] ?? null;
            $document->due_date = isset($data['due_date']) ? \Carbon\Carbon::parse($data['due_date']) : null;
            $document->template = $data['template'] ?? 'classic';
            
            // Calculate totals
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $quantity = floatval($item['quantity'] ?? 0);
                $rate = floatval($item['rate'] ?? 0);
                $subtotal += $quantity * $rate;
            }
            
            $taxRate = floatval($data['tax_rate'] ?? 16); // Default VAT rate
            $discount = floatval($data['discount'] ?? 0);
            
            $document->subtotal = $subtotal;
            $document->discount = $discount;
            $document->tax_rate = $taxRate;
            $document->tax_amount = ($subtotal - $discount) * ($taxRate / 100);
            $document->total = $subtotal - $discount + $document->tax_amount;
            
            $document->save();
            
            // Save items
            foreach ($data['items'] as $item) {
                $quantity = floatval($item['quantity'] ?? 0);
                $rate = floatval($item['rate'] ?? 0);
                
                $document->items()->create([
                    'description' => $item['description'],
                    'quantity' => $quantity,
                    'rate' => $rate,
                    'amount' => $quantity * $rate,
                ]);
            }
            
            // Track usage
            UsageTracking::create([
                'user_id' => $user->id,
                'document_type' => $data['document_type'],
                'template_used' => $document->template,
                'integration_source' => $data['integration_source'] ?? 'internal',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'document' => [
                    'id' => $document->id,
                    'document_number' => $document->document_number,
                    'type' => $document->document_type,
                    'total' => $document->total,
                    'formatted_total' => $document->getFormattedTotal(),
                    'pdf_url' => route('quick-invoice.view', $document->id),
                    'download_url' => route('quick-invoice.download', $document->id),
                    'created_at' => $document->created_at,
                ],
                'remaining_documents' => $subscription->getRemainingDocuments(),
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'error' => 'Failed to create document',
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Get user's subscription status
     */
    public function getUserSubscriptionStatus(User $user): array
    {
        $subscription = UserSubscription::getOrCreateFreeSubscription($user->id);
        $monthlyUsage = UsageTracking::getUserMonthlyUsage($user->id);
        
        return [
            'tier_name' => $subscription->tier->name,
            'tier_price' => $subscription->tier->formatted_price,
            'documents_per_month' => $subscription->tier->documents_per_month,
            'documents_used' => $monthlyUsage,
            'remaining_documents' => $subscription->getRemainingDocuments(),
            'usage_percentage' => $subscription->getUsagePercentage(),
            'can_create_document' => $subscription->canCreateDocument(),
            'features' => $subscription->tier->features,
        ];
    }
    
    /**
     * Get available templates for user
     */
    public function getAvailableTemplates(User $user): array
    {
        $subscription = UserSubscription::getOrCreateFreeSubscription($user->id);
        $templates = TemplateService::getTemplatesForTier($subscription->tier->name);
        
        return [
            'templates' => array_values($templates),
            'user_tier' => $subscription->tier->name,
        ];
    }
    
    /**
     * Get user's recent documents
     */
    public function getUserDocuments(User $user, int $limit = 10): array
    {
        $documents = QuickInvoiceDocument::where('user_id', $user->id)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'document_number' => $doc->document_number,
                    'type' => $doc->document_type,
                    'type_label' => $doc->getTypeLabel(),
                    'client_name' => $doc->client_name,
                    'total' => $doc->total,
                    'formatted_total' => $doc->getFormattedTotal(),
                    'created_at' => $doc->created_at,
                    'created_at_human' => $doc->created_at->diffForHumans(),
                    'pdf_url' => route('quick-invoice.view', $doc->id),
                    'download_url' => route('quick-invoice.download', $doc->id),
                ];
            });
        
        return $documents->toArray();
    }
    
    /**
     * Get user's business profile
     */
    public function getUserProfile(User $user): ?array
    {
        $profile = QuickInvoiceProfile::where('user_id', $user->id)->first();
        
        if (!$profile) {
            return null;
        }
        
        return [
            'name' => $profile->name,
            'email' => $profile->email,
            'phone' => $profile->phone,
            'address' => $profile->address,
            'logo' => $profile->logo,
            'default_template' => $profile->default_template,
            'default_color' => $profile->default_color,
            'default_font' => $profile->default_font,
        ];
    }
    
    /**
     * Check if user can access a specific template
     */
    public function canUserAccessTemplate(User $user, string $templateId): bool
    {
        $subscription = UserSubscription::getOrCreateFreeSubscription($user->id);
        return TemplateService::hasAccessToTemplate($templateId, $subscription->tier->name);
    }
}