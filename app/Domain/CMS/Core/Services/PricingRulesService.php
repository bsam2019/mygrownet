<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\PricingRulesModel;

class PricingRulesService
{
    public function __construct(
        private AuditTrailService $auditTrail
    ) {}

    public function getOrCreate(int $companyId): PricingRulesModel
    {
        return PricingRulesModel::getOrCreateForCompany($companyId);
    }

    public function update(int $companyId, array $data, int $userId): PricingRulesModel
    {
        $rules = PricingRulesModel::getOrCreateForCompany($companyId);
        $old = $rules->toArray();

        $rules->update([
            'sliding_window_rate'    => $data['sliding_window_rate'],
            'casement_window_rate'   => $data['casement_window_rate'],
            'sliding_door_rate'      => $data['sliding_door_rate'],
            'hinged_door_rate'       => $data['hinged_door_rate'],
            'other_rate'             => $data['other_rate'],
            'material_cost_per_m2'   => $data['material_cost_per_m2'],
            'labour_cost_per_m2'     => $data['labour_cost_per_m2'],
            'overhead_cost_per_m2'   => $data['overhead_cost_per_m2'],
            'minimum_profit_percent' => $data['minimum_profit_percent'],
            'tax_rate'               => $data['tax_rate'],
        ]);

        $this->auditTrail->log(
            $companyId,
            $userId,
            'pricing_rules_updated',
            'PricingRules',
            $rules->id,
            $old,
            $rules->fresh()->toArray()
        );

        return $rules->fresh();
    }
}
