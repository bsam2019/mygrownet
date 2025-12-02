<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Module\Entities\Module;
use App\Domain\Module\Repositories\ModuleRepositoryInterface;
use App\Domain\Module\ValueObjects\ModuleConfiguration;
use App\Infrastructure\Persistence\Eloquent\ModuleModel;

class EloquentModuleRepository implements ModuleRepositoryInterface
{
    public function findById(string $id): ?Module
    {
        $model = ModuleModel::find($id);
        
        return $model ? $this->toDomainEntity($model) : null;
    }
    
    public function findBySlug(string $slug): ?Module
    {
        $model = ModuleModel::where('slug', $slug)->first();
        
        return $model ? $this->toDomainEntity($model) : null;
    }
    
    public function findByCategory(string $category): array
    {
        $models = ModuleModel::where('category', $category)
            ->where('status', 'active')
            ->get();
        
        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }
    
    public function findByAccountType(string $accountType): array
    {
        $models = ModuleModel::where('status', 'active')
            ->whereJsonContains('account_types', $accountType)
            ->get();
        
        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }
    
    public function findAvailableForUser(int $userId, string $accountType): array
    {
        $models = ModuleModel::where('status', 'active')
            ->whereJsonContains('account_types', $accountType)
            ->get();
        
        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }
    
    public function findAllActive(): array
    {
        $models = ModuleModel::where('status', 'active')->get();
        
        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }
    
    public function findAll(): array
    {
        $models = ModuleModel::all();
        
        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }
    
    public function delete(string $id): void
    {
        ModuleModel::where('id', $id)->delete();
    }
    
    public function save(Module $module): void
    {
        $data = [
            'id' => $module->getId(),
            'name' => $module->getName(),
            'slug' => $module->getSlug(),
            'category' => $module->getCategory(),
            'description' => $module->getDescription(),
            'icon' => $module->getIcon(),
            'color' => $module->getColor(),
            'thumbnail' => $module->getThumbnail(),
            'account_types' => $module->getAccountTypes(),
            'required_roles' => $module->getRequiredRoles(),
            'min_user_level' => $module->getMinUserLevel(),
            'routes' => $module->getConfiguration()->getRoutes(),
            'pwa_config' => $module->getConfiguration()->getPwaConfig(),
            'features' => $module->getConfiguration()->getFeatures(),
            'subscription_tiers' => $module->getConfiguration()->getSubscriptionTiers(),
            'requires_subscription' => $module->requiresSubscription(),
            'version' => $module->getVersion(),
            'status' => $module->getStatus(),
        ];
        
        ModuleModel::updateOrCreate(['id' => $module->getId()], $data);
    }
    
    private function toDomainEntity(ModuleModel $model): Module
    {
        $configuration = ModuleConfiguration::create(
            icon: $model->icon ?? '',
            color: $model->color ?? '#6366f1',
            routes: $model->routes ?? [],
            pwaConfig: $model->pwa_config ?? [],
            features: $model->features ?? [],
            subscriptionTiers: $model->subscription_tiers ?? [],
            requiresSubscription: $model->requires_subscription ?? true,
            thumbnail: $model->thumbnail
        );
        
        return Module::reconstitute(
            id: \App\Domain\Module\ValueObjects\ModuleId::fromString($model->id),
            name: \App\Domain\Module\ValueObjects\ModuleName::fromString($model->name),
            slug: \App\Domain\Module\ValueObjects\ModuleSlug::fromString($model->slug),
            category: \App\Domain\Module\ValueObjects\ModuleCategory::from($model->category),
            description: $model->description ?? '',
            accountTypes: array_map(
                fn($type) => \App\Enums\AccountType::from($type),
                $model->account_types ?? []
            ),
            configuration: $configuration,
            status: \App\Domain\Module\ValueObjects\ModuleStatus::from($model->status),
            version: $model->version ?? '1.0.0',
            createdAt: new \DateTimeImmutable($model->created_at),
            updatedAt: new \DateTimeImmutable($model->updated_at)
        );
    }
}
