<?php

namespace App\Domain\BizDocs\DocumentManagement\Services;

use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentSequenceRepositoryInterface;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentNumber;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentType;
use Illuminate\Support\Facades\DB;

class DocumentNumberingService
{
    public function __construct(
        private readonly DocumentSequenceRepositoryInterface $sequenceRepository
    ) {
    }

    public function generateNextNumber(
        int $businessId,
        DocumentType $documentType,
        ?string $customPrefix = null,
        int $padding = 4,
        bool $includeYear = false
    ): DocumentNumber {
        return DB::transaction(function () use ($businessId, $documentType, $customPrefix, $padding, $includeYear) {
            $year = now()->year;
            $prefix = $customPrefix ?? $documentType->defaultPrefix();

            $sequence = $this->sequenceRepository->lockForUpdate($businessId, $documentType->value(), $year);

            if (!$sequence) {
                $sequence = $this->sequenceRepository->create($businessId, $documentType->value(), $year, $prefix, $padding, $includeYear);
            }

            $nextNumber = ($sequence['last_number'] ?? 0) + 1;
            $this->sequenceRepository->updateNextNumber($sequence['id'], $nextNumber);

            $useYear = $sequence['include_year'] ?? $includeYear;

            return DocumentNumber::generate($prefix, $useYear ? $year : null, $nextNumber, $padding);
        });
    }

    public function getLastNumber(int $businessId, DocumentType $documentType): int
    {
        $year = now()->year;

        $sequence = $this->sequenceRepository->find($businessId, $documentType->value(), $year);

        return $sequence['last_number'] ?? 0;
    }

    public function resetSequence(int $businessId, DocumentType $documentType, int $startNumber = 0): void
    {
        $year = now()->year;

        $this->sequenceRepository->updateOrCreate(
            [
                'business_id' => $businessId,
                'document_type' => $documentType->value(),
                'year' => $year,
            ],
            [
                'last_number' => $startNumber,
            ]
        );
    }
}