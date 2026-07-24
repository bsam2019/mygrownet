<?php

namespace App\Infrastructure\BizDocs\Persistence\Repositories;

use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentSequenceRepositoryInterface;
use App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentSequenceModel;

class EloquentDocumentSequenceRepository implements DocumentSequenceRepositoryInterface
{
    public function lockForUpdate(int $businessId, string $documentType, int $year): ?array
    {
        $sequence = DocumentSequenceModel::lockForUpdate()
            ->where('business_id', $businessId)
            ->where('document_type', $documentType)
            ->where('year', $year)
            ->first();

        return $sequence ? $sequence->toArray() : null;
    }

    public function create(int $businessId, string $documentType, int $year, string $prefix, int $padding, bool $includeYear): array
    {
        return DocumentSequenceModel::create([
            'business_id' => $businessId,
            'document_type' => $documentType,
            'year' => $year,
            'last_number' => 0,
            'prefix' => $prefix,
            'padding' => $padding,
            'include_year' => $includeYear,
        ])->toArray();
    }

    public function find(int $businessId, string $documentType, int $year): ?array
    {
        $sequence = DocumentSequenceModel::where('business_id', $businessId)
            ->where('document_type', $documentType)
            ->where('year', $year)
            ->first();

        return $sequence ? $sequence->toArray() : null;
    }

    public function updateNextNumber(int $id, int $nextNumber): void
    {
        DocumentSequenceModel::where('id', $id)->update(['last_number' => $nextNumber]);
    }

    public function updateOrCreate(array $attributes, array $values): void
    {
        DocumentSequenceModel::updateOrCreate($attributes, $values);
    }
}