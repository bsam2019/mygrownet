<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\QuickInvoice;

use App\Domain\QuickInvoice\Repositories\SubscriptionTierRepositoryInterface;
use App\Models\QuickInvoice\SubscriptionTier;

class EloquentSubscriptionTierRepository implements SubscriptionTierRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $model = SubscriptionTier::find($id);
        return $model ? $model->toArray() : null;
    }

    public function findByName(string $name): ?array
    {
        $model = SubscriptionTier::where('name', $name)->first();
        return $model ? $model->toArray() : null;
    }

    public function getFreeTier(): array
    {
        $model = SubscriptionTier::where('name', 'Free')->where('is_active', true)->first()
            ?? SubscriptionTier::create([
                'name' => 'Free',
                'price' => 0,
                'currency' => 'ZMW',
                'documents_per_month' => -1,
                'features' => [
                    'templates' => 'all',
                    'sharing' => ['pdf_download', 'email', 'whatsapp'],
                    'watermark' => false,
                    'customization' => true,
                    'api_access' => true,
                    'priority_support' => true,
                    'custom_branding' => true,
                    'advanced_templates' => true,
                    'custom_fields' => true,
                    'design_studio' => true,
                    'white_label' => true,
                    'advanced_analytics' => true,
                    'cms_integration' => true,
                ],
                'is_active' => true,
            ]);

        return $model->toArray();
    }

    public function findAllActive(): array
    {
        return SubscriptionTier::where('is_active', true)
            ->orderBy('price')
            ->get()
            ->toArray();
    }

    public function findAll(): array
    {
        return SubscriptionTier::orderBy('price')
            ->get()
            ->toArray();
    }

    public function create(array $data): array
    {
        $model = SubscriptionTier::create($data);
        return $model->toArray();
    }

    public function update(int $id, array $data): ?array
    {
        $model = SubscriptionTier::find($id);
        if (!$model) {
            return null;
        }

        $model->update($data);
        return $model->fresh()->toArray();
    }

    public function delete(int $id): bool
    {
        return SubscriptionTier::destroy($id) > 0;
    }

    public function hasSubscribers(int $id): bool
    {
        return SubscriptionTier::find($id)?->userSubscriptions()->exists() ?? false;
    }
}