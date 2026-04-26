<?php

namespace App\Domain\CMS\BOQ\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\BOQModel;
use App\Infrastructure\Persistence\Eloquent\CMS\BOQItemModel;
use App\Infrastructure\Persistence\Eloquent\CMS\BOQVariationModel;
use Illuminate\Support\Facades\DB;

class BOQService
{
    public function generateBOQNumber(int $companyId): string
    {
        $year = date('Y');
        $lastBOQ = BOQModel::where('company_id', $companyId)
            ->where('boq_number', 'like', "BOQ-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastBOQ) {
            $lastNumber = (int) substr($lastBOQ->boq_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "BOQ-{$year}-{$newNumber}";
    }

    public function createBOQ(array $data): BOQModel
    {
        return DB::transaction(function () use ($data) {
            $boq = BOQModel::create([
                'company_id' => $data['company_id'],
                'project_id' => $data['project_id'] ?? null,
                'quotation_id' => $data['quotation_id'] ?? null,
                'template_id' => $data['template_id'] ?? null,
                'boq_number' => $data['boq_number'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'contingency_percentage' => $data['contingency_percentage'] ?? 0,
                'prepared_by' => $data['prepared_by'],
                'prepared_date' => $data['prepared_date'] ?? now(),
            ]);

            // Add items if provided
            if (!empty($data['items'])) {
                foreach ($data['items'] as $index => $item) {
                    BOQItemModel::create([
                        'boq_id' => $boq->id,
                        'category_id' => $item['category_id'] ?? null,
                        'item_code' => $item['item_code'],
                        'description' => $item['description'],
                        'unit' => $item['unit'],
                        'quantity' => $item['quantity'],
                        'unit_rate' => $item['unit_rate'],
                        'sort_order' => $index + 1,
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }

            $boq->recalculateTotals();
            return $boq->fresh(['items', 'project']);
        });
    }

    public function addItem(BOQModel $boq, array $data): BOQItemModel
    {
        $maxOrder = $boq->items()->max('sort_order') ?? 0;

        $item = BOQItemModel::create([
            'boq_id' => $boq->id,
            'category_id' => $data['category_id'] ?? null,
            'item_code' => $data['item_code'],
            'description' => $data['description'],
            'unit' => $data['unit'],
            'quantity' => $data['quantity'],
            'unit_rate' => $data['unit_rate'],
            'sort_order' => $maxOrder + 1,
            'notes' => $data['notes'] ?? null,
        ]);

        $boq->recalculateTotals();
        return $item;
    }

    public function updateItem(BOQItemModel $item, array $data): BOQItemModel
    {
        $item->update($data);
        $item->boq->recalculateTotals();
        return $item;
    }

    public function deleteItem(BOQItemModel $item): void
    {
        $boq = $item->boq;
        $item->delete();
        $boq->recalculateTotals();
    }

    public function addVariation(BOQModel $boq, array $data): BOQVariationModel
    {
        $variationNumber = $this->generateVariationNumber($boq);

        return BOQVariationModel::create([
            'boq_id' => $boq->id,
            'variation_number' => $variationNumber,
            'description' => $data['description'],
            'type' => $data['type'],
            'amount' => $data['amount'],
            'date_raised' => $data['date_raised'] ?? now(),
        ]);
    }

    public function approveVariation(BOQVariationModel $variation, int $approvedBy): BOQVariationModel
    {
        $variation->status = 'approved';
        $variation->approved_by = $approvedBy;
        $variation->approved_date = now();
        $variation->approval_notes = request('approval_notes');
        $variation->save();

        return $variation;
    }

    private function generateVariationNumber(BOQModel $boq): string
    {
        $lastVariation = $boq->variations()->orderBy('id', 'desc')->first();
        
        if ($lastVariation) {
            $lastNumber = (int) substr($lastVariation->variation_number, -2);
            $newNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '01';
        }

        return "{$boq->boq_number}-VAR-{$newNumber}";
    }

    public function getBOQSummary(BOQModel $boq): array
    {
        $items = $boq->items;
        $variations = $boq->variations()->where('status', 'approved')->get();

        $totalVariations = $variations->sum(function ($v) {
            return $v->type === 'addition' ? $v->amount : -$v->amount;
        });

        $actualTotal = $items->sum('actual_amount');
        $variance = $actualTotal - $boq->total_amount;

        return [
            'original_total' => $boq->total_amount,
            'contingency' => $boq->contingency_amount,
            'variations_total' => $totalVariations,
            'revised_total' => $boq->grand_total + $totalVariations,
            'actual_total' => $actualTotal,
            'variance' => $variance,
            'variance_percentage' => $boq->total_amount > 0 ? ($variance / $boq->total_amount) * 100 : 0,
            'items_count' => $items->count(),
            'variations_count' => $variations->count(),
        ];
    }
}
