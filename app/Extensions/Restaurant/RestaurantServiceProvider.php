<?php

namespace App\Extensions\Restaurant;

use App\Extensions\ExtensionServiceProvider;
use App\Extensions\Restaurant\Repositories\RecipeRepositoryInterface;
use App\Extensions\Restaurant\Repositories\EloquentRecipeRepository;
use App\Extensions\Restaurant\Repositories\WastageRepositoryInterface;
use App\Extensions\Restaurant\Repositories\EloquentWastageRepository;
use App\Extensions\Restaurant\Services\RestaurantService;

class RestaurantServiceProvider extends ExtensionServiceProvider
{
    public function getCode(): string { return 'restaurant'; }

    public function getName(): string { return 'Restaurant Extension'; }

    public function getDescription(): ?string
    {
        return 'Recipe management with ingredient lists and yield tracking, menu costing with real-time ingredient prices, wastage/trim tracking, and allergen/dietary labeling for restaurants, catering, and hospitality.';
    }

    public function getVersion(): ?string { return '1.0.0'; }

    public function getFeatures(): array
    {
        return ['recipes', 'menu-costing', 'wastage'];
    }

    public function getDefaultSettings(): array
    {
        return ['auto_cost_recipes' => true, 'track_prep_waste' => true, 'enable_allergen_labels' => false];
    }

    public function boot(): void
    {
        $this->loadExtensionMigrations();
        $this->loadExtensionRoutes();
    }

    public function register(): void
    {
        $this->registerBindings([
            RecipeRepositoryInterface::class => EloquentRecipeRepository::class,
            WastageRepositoryInterface::class => EloquentWastageRepository::class,
        ]);
        $this->registerServices([RestaurantService::class]);
    }
}
