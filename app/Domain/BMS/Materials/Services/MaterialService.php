<?php

namespace App\Domain\CMS\Materials\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\MaterialModel;
use App\Infrastructure\Persistence\Eloquent\CMS\MaterialCategoryModel;
use Illuminate\Support\Facades\DB;

class MaterialService
{
    public function createMaterial(array $data): MaterialModel
    {
        return DB::transaction(function () use ($data) {
            $material = MaterialModel::create([
                'company_id' => $data['company_id'],
                'category_id' => $data['category_id'] ?? null,
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'unit' => $data['unit'],
                'current_price' => $data['current_price'] ?? 0,
                'minimum_stock' => $data['minimum_stock'] ?? null,
                'reorder_level' => $data['reorder_level'] ?? null,
                'supplier' => $data['supplier'] ?? null,
                'supplier_code' => $data['supplier_code'] ?? null,
                'lead_time_days' => $data['lead_time_days'] ?? null,
                'specifications' => $data['specifications'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $material;
        });
    }

    public function updateMaterial(MaterialModel $material, array $data): MaterialModel
    {
        return DB::transaction(function () use ($material, $data) {
            // Handle price update separately to track history
            if (isset($data['current_price']) && $data['current_price'] != $material->current_price) {
                $material->updatePrice(
                    $data['current_price'],
                    $data['changed_by'],
                    $data['price_change_reason'] ?? null
                );
                unset($data['current_price']);
            }

            $material->update($data);
            return $material->fresh();
        });
    }

    public function deleteMaterial(MaterialModel $material): bool
    {
        return $material->delete();
    }

    public function createCategory(array $data): MaterialCategoryModel
    {
        return MaterialCategoryModel::create($data);
    }

    public function updateCategory(MaterialCategoryModel $category, array $data): MaterialCategoryModel
    {
        $category->update($data);
        return $category->fresh();
    }

    public function deleteCategory(MaterialCategoryModel $category): bool
    {
        // Check if category has materials
        if ($category->materials()->exists()) {
            throw new \Exception('Cannot delete category with existing materials');
        }

        return $category->delete();
    }

    public function bulkUpdatePrices(array $materialIds, float $percentageChange, int $changedBy, string $reason): int
    {
        $updated = 0;

        foreach ($materialIds as $materialId) {
            $material = MaterialModel::find($materialId);
            if ($material) {
                $newPrice = $material->current_price * (1 + ($percentageChange / 100));
                $material->updatePrice($newPrice, $changedBy, $reason);
                $updated++;
            }
        }

        return $updated;
    }

    public function importMaterials(int $companyId, array $materials, int $createdBy): array
    {
        $imported = 0;
        $errors = [];

        DB::transaction(function () use ($companyId, $materials, &$imported, &$errors) {
            foreach ($materials as $index => $materialData) {
                try {
                    MaterialModel::create([
                        'company_id' => $companyId,
                        'category_id' => $materialData['category_id'] ?? null,
                        'code' => $materialData['code'],
                        'name' => $materialData['name'],
                        'description' => $materialData['description'] ?? null,
                        'unit' => $materialData['unit'],
                        'current_price' => $materialData['current_price'] ?? 0,
                        'supplier' => $materialData['supplier'] ?? null,
                        'is_active' => true,
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$index}: " . $e->getMessage();
                }
            }
        });

        return [
            'imported' => $imported,
            'errors' => $errors,
        ];
    }
}
