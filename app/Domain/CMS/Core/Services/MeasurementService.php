<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\MeasurementRecordModel;
use App\Infrastructure\Persistence\Eloquent\CMS\MeasurementItemModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PricingRulesModel;
use App\Infrastructure\Persistence\Eloquent\CMS\QuotationModel;
use App\Infrastructure\Persistence\Eloquent\CMS\QuotationItemModel;
use Illuminate\Support\Facades\DB;

class MeasurementService
{
    public function __construct(
        private AuditTrailService $auditTrail
    ) {}

    /**
     * Create a new measurement record
     */
    public function createMeasurement(array $data): MeasurementRecordModel
    {
        return DB::transaction(function () use ($data) {
            $measurement = MeasurementRecordModel::create([
                'company_id'         => $data['company_id'],
                'customer_id'        => $data['customer_id'],
                'measurement_number' => MeasurementRecordModel::generateMeasurementNumber($data['company_id']),
                'project_name'       => $data['project_name'],
                'location'           => $data['location'] ?? null,
                'measured_by'        => $data['measured_by'] ?? null,
                'measurement_date'   => $data['measurement_date'],
                'status'             => 'draft',
                'notes'              => $data['notes'] ?? null,
                'created_by'         => $data['created_by'],
            ]);

            // Create items if provided
            if (!empty($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $this->addItem($measurement, $itemData);
                }
            }

            $this->auditTrail->log(
                $data['company_id'],
                $data['created_by'],
                'measurement_created',
                'MeasurementRecord',
                $measurement->id,
                null,
                $measurement->toArray()
            );

            return $measurement->load(['customer', 'items']);
        });
    }

    /**
     * Update a measurement record
     */
    public function updateMeasurement(MeasurementRecordModel $measurement, array $data): MeasurementRecordModel
    {
        return DB::transaction(function () use ($measurement, $data) {
            $old = $measurement->toArray();

            $measurement->update([
                'customer_id'      => $data['customer_id'] ?? $measurement->customer_id,
                'project_name'     => $data['project_name'] ?? $measurement->project_name,
                'location'         => $data['location'] ?? $measurement->location,
                'measured_by'      => $data['measured_by'] ?? $measurement->measured_by,
                'measurement_date' => $data['measurement_date'] ?? $measurement->measurement_date,
                'notes'            => $data['notes'] ?? $measurement->notes,
            ]);

            $this->auditTrail->log(
                $measurement->company_id,
                $data['updated_by'],
                'measurement_updated',
                'MeasurementRecord',
                $measurement->id,
                $old,
                $measurement->fresh()->toArray()
            );

            return $measurement->fresh()->load(['customer', 'items']);
        });
    }

    /**
     * Add an item to a measurement
     */
    public function addItem(MeasurementRecordModel $measurement, array $data): MeasurementItemModel
    {
        $item = new MeasurementItemModel([
            'company_id'      => $measurement->company_id,
            'measurement_id'  => $measurement->id,
            'location_name'   => $data['location_name'],
            'type'            => $data['type'],
            'width_top'       => $data['width_top'],
            'width_middle'    => $data['width_middle'],
            'width_bottom'    => $data['width_bottom'],
            'height_left'     => $data['height_left'],
            'height_right'    => $data['height_right'],
            'quantity'        => $data['quantity'] ?? 1,
            'notes'           => $data['notes'] ?? null,
        ]);

        // calculateDimensions() is called automatically via model boot
        $item->save();

        return $item;
    }

    /**
     * Update a measurement item
     */
    public function updateItem(MeasurementItemModel $item, array $data): MeasurementItemModel
    {
        $item->fill([
            'location_name' => $data['location_name'] ?? $item->location_name,
            'type'          => $data['type'] ?? $item->type,
            'width_top'     => $data['width_top'] ?? $item->width_top,
            'width_middle'  => $data['width_middle'] ?? $item->width_middle,
            'width_bottom'  => $data['width_bottom'] ?? $item->width_bottom,
            'height_left'   => $data['height_left'] ?? $item->height_left,
            'height_right'  => $data['height_right'] ?? $item->height_right,
            'quantity'      => $data['quantity'] ?? $item->quantity,
            'notes'         => $data['notes'] ?? $item->notes,
        ]);

        // calculateDimensions() is called automatically via model boot
        $item->save();

        return $item;
    }

    /**
     * Mark measurement as completed
     */
    public function completeMeasurement(MeasurementRecordModel $measurement, int $userId): MeasurementRecordModel
    {
        $measurement->update(['status' => 'completed']);

        $this->auditTrail->log(
            $measurement->company_id,
            $userId,
            'measurement_completed',
            'MeasurementRecord',
            $measurement->id,
            ['status' => 'draft'],
            ['status' => 'completed']
        );

        return $measurement;
    }

    /**
     * Generate a quotation from a measurement
     */
    public function generateQuotation(MeasurementRecordModel $measurement, int $userId): QuotationModel
    {
        return DB::transaction(function () use ($measurement, $userId) {
            $companyId = $measurement->company_id;

            // Get tenant pricing rules
            $pricing = PricingRulesModel::getOrCreateForCompany($companyId);

            // Calculate totals per item
            $quotationItems = [];
            $subtotal = 0;

            foreach ($measurement->items as $item) {
                $sellingPrice = $pricing->calculateSellingPrice($item->type, (float)$item->total_area);
                $subtotal += $sellingPrice;

                $quotationItems[] = [
                    'description'      => sprintf(
                        '%s – %s',
                        $item->getTypeLabel(),
                        $item->location_name
                    ),
                    'quantity'         => $item->quantity,
                    'unit_price'       => $pricing->getRateForType($item->type),
                    'tax_rate'         => 0,
                    'discount_rate'    => 0,
                    'line_total'       => $sellingPrice,
                    'dimensions'       => $item->getFormattedDimensions(),
                    'dimensions_value' => (float)$item->total_area,
                ];
            }

            $taxAmount = $subtotal * ((float)$pricing->tax_rate / 100);
            $totalAmount = $subtotal + $taxAmount;

            // Generate quotation number
            $year = date('Y');
            $lastQuotation = QuotationModel::where('company_id', $companyId)
                ->where('quotation_number', 'like', "QUO-{$year}-%")
                ->orderBy('id', 'desc')
                ->first();
            $seq = $lastQuotation ? ((int) substr($lastQuotation->quotation_number, -4)) + 1 : 1;
            $quotationNumber = 'QUO-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

            // Get company document defaults (notes/terms)
            $settingsService = app(\App\Domain\CMS\Core\Services\CompanySettingsService::class);
            $docDefaults = $settingsService->getDocumentDefaults($companyId, 'quotation');

            // Create quotation
            $quotation = QuotationModel::create([
                'company_id'       => $companyId,
                'customer_id'      => $measurement->customer_id,
                'measurement_id'   => $measurement->id,
                'quotation_number' => $quotationNumber,
                'quotation_date'   => now()->toDateString(),
                'expiry_date'      => now()->addDays(30)->toDateString(),
                'subtotal'         => $subtotal,
                'tax_amount'       => $taxAmount,
                'discount_amount'  => 0,
                'total_amount'     => $totalAmount,
                'status'           => 'draft',
                'notes'            => $docDefaults['notes'] ?: "Generated from measurement {$measurement->measurement_number} – {$measurement->project_name}",
                'terms'            => $docDefaults['terms'] ?: null,
                'created_by'       => $userId,
            ]);

            // Create quotation items
            foreach ($quotationItems as $itemData) {
                QuotationItemModel::create([
                    'quotation_id'     => $quotation->id,
                    'description'      => $itemData['description'],
                    'quantity'         => $itemData['quantity'],
                    'unit_price'       => $itemData['unit_price'],
                    'tax_rate'         => $itemData['tax_rate'],
                    'discount_rate'    => $itemData['discount_rate'],
                    'line_total'       => $itemData['line_total'],
                    'dimensions'       => $itemData['dimensions'] ?? null,
                    'dimensions_value' => $itemData['dimensions_value'] ?? 1,
                ]);
            }

            // Mark measurement as quoted
            $measurement->update(['status' => 'quoted']);

            $this->auditTrail->log(
                $companyId,
                $userId,
                'quotation_generated_from_measurement',
                'QuotationModel',
                $quotation->id,
                null,
                ['measurement_id' => $measurement->id, 'total' => $totalAmount]
            );

            return $quotation->load(['customer', 'items', 'measurement']);
        });
    }

    /**
     * Get profit summary for a measurement (internal use only)
     */
    public function getProfitSummary(MeasurementRecordModel $measurement): array
    {
        $pricing = PricingRulesModel::getOrCreateForCompany($measurement->company_id);

        $totalRevenue = 0;
        $totalCost = 0;
        $items = [];

        foreach ($measurement->items as $item) {
            $profitData = $pricing->calculateProfit($item->type, (float)$item->total_area);
            $totalRevenue += $profitData['selling_price'];
            $totalCost += $profitData['cost'];

            $items[] = [
                'location'       => $item->location_name,
                'type'           => $item->getTypeLabel(),
                'area'           => $item->total_area,
                'selling_price'  => $profitData['selling_price'],
                'cost'           => $profitData['cost'],
                'profit'         => $profitData['profit'],
                'profit_percent' => $profitData['profit_percent'],
                'meets_minimum'  => $profitData['meets_minimum'],
            ];
        }

        $totalProfit = $totalRevenue - $totalCost;
        $overallProfitPercent = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        return [
            'total_revenue'          => $totalRevenue,
            'total_cost'             => $totalCost,
            'total_profit'           => $totalProfit,
            'overall_profit_percent' => $overallProfitPercent,
            'meets_minimum'          => $overallProfitPercent >= (float)$pricing->minimum_profit_percent,
            'minimum_required'       => $pricing->minimum_profit_percent,
            'items'                  => $items,
        ];
    }
}
