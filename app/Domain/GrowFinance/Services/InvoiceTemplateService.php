<?php

namespace App\Domain\GrowFinance\Services;

use App\Domain\Module\Services\SubscriptionService;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceTemplateModel;
use App\Models\User;

class InvoiceTemplateService
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Get all templates for a business
     */
    public function getTemplates(int $businessId): array
    {
        return GrowFinanceInvoiceTemplateModel::forBusiness($businessId)
            ->active()
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    /**
     * Get default template for a business
     */
    public function getDefaultTemplate(int $businessId): ?GrowFinanceInvoiceTemplateModel
    {
        return GrowFinanceInvoiceTemplateModel::forBusiness($businessId)
            ->active()
            ->default()
            ->first();
    }

    /**
     * Check if user can create more templates
     */
    public function canCreateTemplate(User $user): array
    {
        $limits = $this->subscriptionService->getUserLimits($user);
        $maxTemplates = $limits['invoice_templates'] ?? 0;

        if ($maxTemplates === 0) {
            return [
                'allowed' => false,
                'reason' => 'Custom invoice templates are available on Professional plan and above.',
            ];
        }

        $currentCount = GrowFinanceInvoiceTemplateModel::forBusiness($user->id)
            ->active()
            ->count();

        if ($maxTemplates !== -1 && $currentCount >= $maxTemplates) {
            return [
                'allowed' => false,
                'reason' => "Template limit reached ({$maxTemplates}). Upgrade for unlimited templates.",
                'used' => $currentCount,
                'limit' => $maxTemplates,
            ];
        }

        return [
            'allowed' => true,
            'used' => $currentCount,
            'limit' => $maxTemplates,
        ];
    }

    /**
     * Create a new template
     */
    public function createTemplate(int $businessId, array $data): GrowFinanceInvoiceTemplateModel
    {
        return GrowFinanceInvoiceTemplateModel::create([
            'business_id' => $businessId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'layout' => $data['layout'] ?? GrowFinanceInvoiceTemplateModel::LAYOUT_STANDARD,
            'colors' => $data['colors'] ?? GrowFinanceInvoiceTemplateModel::DEFAULT_COLORS,
            'fonts' => $data['fonts'] ?? GrowFinanceInvoiceTemplateModel::DEFAULT_FONTS,
            'logo_position' => $data['logo_position'] ?? 'left',
            'show_logo' => $data['show_logo'] ?? true,
            'show_watermark' => $data['show_watermark'] ?? false,
            'header_text' => $data['header_text'] ?? null,
            'footer_text' => $data['footer_text'] ?? null,
            'terms_text' => $data['terms_text'] ?? null,
            'custom_fields' => $data['custom_fields'] ?? null,
            'is_default' => $data['is_default'] ?? false,
        ]);
    }

    /**
     * Update a template
     */
    public function updateTemplate(int $businessId, int $templateId, array $data): GrowFinanceInvoiceTemplateModel
    {
        $template = GrowFinanceInvoiceTemplateModel::forBusiness($businessId)
            ->findOrFail($templateId);

        $template->update([
            'name' => $data['name'] ?? $template->name,
            'description' => $data['description'] ?? $template->description,
            'layout' => $data['layout'] ?? $template->layout,
            'colors' => $data['colors'] ?? $template->colors,
            'fonts' => $data['fonts'] ?? $template->fonts,
            'logo_position' => $data['logo_position'] ?? $template->logo_position,
            'show_logo' => $data['show_logo'] ?? $template->show_logo,
            'show_watermark' => $data['show_watermark'] ?? $template->show_watermark,
            'header_text' => $data['header_text'] ?? $template->header_text,
            'footer_text' => $data['footer_text'] ?? $template->footer_text,
            'terms_text' => $data['terms_text'] ?? $template->terms_text,
            'custom_fields' => $data['custom_fields'] ?? $template->custom_fields,
            'is_default' => $data['is_default'] ?? $template->is_default,
        ]);

        return $template->fresh();
    }

    /**
     * Delete a template
     */
    public function deleteTemplate(int $businessId, int $templateId): bool
    {
        $template = GrowFinanceInvoiceTemplateModel::forBusiness($businessId)
            ->findOrFail($templateId);

        // Don't allow deleting the only template
        $count = GrowFinanceInvoiceTemplateModel::forBusiness($businessId)->active()->count();
        if ($count <= 1) {
            return false;
        }

        // If deleting default, make another one default
        if ($template->is_default) {
            GrowFinanceInvoiceTemplateModel::forBusiness($businessId)
                ->where('id', '!=', $templateId)
                ->active()
                ->first()
                ?->update(['is_default' => true]);
        }

        $template->update(['is_active' => false]);

        return true;
    }

    /**
     * Set template as default
     */
    public function setAsDefault(int $businessId, int $templateId): GrowFinanceInvoiceTemplateModel
    {
        $template = GrowFinanceInvoiceTemplateModel::forBusiness($businessId)
            ->findOrFail($templateId);

        $template->update(['is_default' => true]);

        return $template->fresh();
    }

    /**
     * Get available layouts
     */
    public function getAvailableLayouts(): array
    {
        return GrowFinanceInvoiceTemplateModel::LAYOUTS;
    }

    /**
     * Create default template for new business
     */
    public function createDefaultTemplate(int $businessId): GrowFinanceInvoiceTemplateModel
    {
        return $this->createTemplate($businessId, [
            'name' => 'Default Template',
            'description' => 'Standard invoice template',
            'layout' => GrowFinanceInvoiceTemplateModel::LAYOUT_STANDARD,
            'is_default' => true,
        ]);
    }
}
