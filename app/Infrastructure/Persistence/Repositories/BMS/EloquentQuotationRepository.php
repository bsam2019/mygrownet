<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\Quotation;
use App\Domain\BMS\Repositories\QuotationRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\QuotationModel;

class EloquentQuotationRepository implements QuotationRepositoryInterface
{
    public function findById(int $id): ?Quotation
    {
        $model = QuotationModel::find($id);
        return $model ? Quotation::reconstitute($model->toArray()) : null;
    }

    public function save(Quotation $entity): Quotation
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            QuotationModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = QuotationModel::create($data);
        return Quotation::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return QuotationModel::where('company_id', $companyId)->get()
            ->map(fn($m) => Quotation::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByCustomer(int $customerId): array
    {
        return QuotationModel::where('customer_id', $customerId)->get()
            ->map(fn($m) => Quotation::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByStatus(int $companyId, string $status): array
    {
        return QuotationModel::where('company_id', $companyId)->where('status', $status)->get()
            ->map(fn($m) => Quotation::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByNumber(int $companyId, string $number): ?Quotation
    {
        $model = QuotationModel::where('company_id', $companyId)->where('quotation_number', $number)->first();
        return $model ? Quotation::reconstitute($model->toArray()) : null;
    }

    public function getSummary(int $companyId): array
    {
        $quotations = QuotationModel::where('company_id', $companyId)->get();

        return [
            'total_quotations' => $quotations->count(),
            'draft_count' => $quotations->where('status', 'draft')->count(),
            'sent_count' => $quotations->where('status', 'sent')->count(),
            'accepted_count' => $quotations->where('status', 'accepted')->count(),
            'total_value' => $quotations->sum('total_amount'),
        ];
    }
}
