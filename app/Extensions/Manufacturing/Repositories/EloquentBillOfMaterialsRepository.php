<?php

namespace App\Extensions\Manufacturing\Repositories;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Extensions\Manufacturing\Entities\BillOfMaterials;
use App\Extensions\Manufacturing\Entities\BomMaterial;
use App\Extensions\Manufacturing\Models\SaBillOfMaterialsModel;
use App\Extensions\Manufacturing\ValueObjects\BillOfMaterialsId;
use DateTimeImmutable;

class EloquentBillOfMaterialsRepository implements BillOfMaterialsRepositoryInterface
{
    public function findById(BillOfMaterialsId $id): ?BillOfMaterials
    {
        $model = SaBillOfMaterialsModel::with('materials')->find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompany(CompanyId $companyId): array
    {
        return SaBillOfMaterialsModel::with('materials')->where('sa_company_id', $companyId->toInt())->orderByDesc('created_at')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findByItem(CompanyId $companyId, int $itemId): array
    {
        return SaBillOfMaterialsModel::with('materials')->where('sa_company_id', $companyId->toInt())->where('sa_item_id', $itemId)->orderByDesc('created_at')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function save(BillOfMaterials $bom): BillOfMaterials
    {
        $data = $bom->toArray();
        $materialsData = $data['materials'] ?? [];
        unset($data['id'], $data['materials'], $data['created_at'], $data['updated_at']);

        if ($bom->id() === 0) {
            $model = SaBillOfMaterialsModel::create($data);
        } else {
            $model = SaBillOfMaterialsModel::find($bom->id());
            $model->update($data);
        }

        if ($bom->id() === 0) {
            foreach ($materialsData as $mat) {
                $mat['sa_bom_id'] = $model->id;
                $model->materials()->create($mat);
            }
        }

        return $this->toDomainEntity($model->fresh()->load('materials'));
    }

    public function delete(BillOfMaterialsId $id): void
    {
        SaBillOfMaterialsModel::find($id->toInt())?->delete();
    }

    private function toDomainEntity(SaBillOfMaterialsModel $model): BillOfMaterials
    {
        $materials = $model->materials->map(fn($m) => new BomMaterial(
            itemId: $m->sa_item_id, quantity: (float) $m->quantity, uom: $m->uom,
            wasteFactor: (float) $m->waste_factor, sortOrder: $m->sort_order,
        ))->all();

        return BillOfMaterials::reconstitute(
            id: BillOfMaterialsId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            itemId: ItemId::fromInt($model->sa_item_id),
            name: $model->name, quantity: (float) $model->quantity, uom: $model->uom,
            status: $model->status, version: $model->version, notes: $model->notes,
            materials: $materials,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
