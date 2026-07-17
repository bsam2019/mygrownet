<?php

namespace App\Extensions\Pharmacy;

use App\Extensions\ExtensionServiceProvider;

class PharmacyServiceProvider extends ExtensionServiceProvider
{
    public function getCode(): string
    {
        return 'pharmacy';
    }

    public function getName(): string
    {
        return 'Pharmacy Extension';
    }

    public function getDescription(): ?string
    {
        return 'Controlled medicines tracking, prescription management, batch traceability, and FEFO (First Expiry First Out) dispensing for pharmacies and chemists.';
    }

    public function getVersion(): ?string
    {
        return '1.0.0';
    }

    public function getFeatures(): array
    {
        return [
            'controlled-medicines',
            'prescriptions',
        ];
    }

    public function getDefaultSettings(): array
    {
        return [
            'require_prescription_for_controlled' => true,
            'auto_generate_prescription_numbers' => true,
            'enable_fefo_dispensing' => true,
        ];
    }

    public function boot(): void
    {
        $this->loadExtensionMigrations();
        $this->loadExtensionRoutes();
    }

    public function register(): void
    {
        // Repository bindings will be moved here from StockFlowServiceProvider
        // $this->registerBindings([
        //     ControlledMedicineRepositoryInterface::class => EloquentControlledMedicineRepository::class,
        // ]);

        // $this->registerServices([
        //     ControlledMedicineService::class,
        // ]);
    }
}
