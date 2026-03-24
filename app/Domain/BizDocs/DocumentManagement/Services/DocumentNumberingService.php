<?php

namespace App\Domain\BizDocs\DocumentManagement\Services;

use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentNumber;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentType;
use App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentSequenceModel;
use Illuminate\Support\Facades\DB;

class DocumentNumberingService
{
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

            // Lock row for update to prevent race conditions
            $sequence = DocumentSequenceModel::lockForUpdate()
                ->where('business_id', $businessId)
                ->where('document_type', $documentType->value())
                ->where('year', $year)
                ->first();

            if (!$sequence) {
                $sequence = DocumentSequenceModel::create([
                    'business_id' => $businessId,
                    'document_type' => $documentType->value(),
                    'year' => $year,
                    'last_number' => 0,
                    'prefix' => $prefix,
                    'padding' => $padding,
                    'include_year' => $includeYear,
                ]);
            }

            $nextNumber = $sequence->last_number + 1;
            $sequence->update(['last_number' => $nextNumber]);

            // Use the sequence's include_year setting
            $useYear = $sequence->include_year;

            return DocumentNumber::generate($prefix, $useYear ? $year : null, $nextNumber, $padding);
        });
    }

    public function getLastNumber(int $businessId, DocumentType $documentType): int
    {
        $year = now()->year;

        $sequence = DocumentSequenceModel::where('business_id', $businessId)
            ->where('document_type', $documentType->value())
            ->where('year', $year)
            ->first();

        return $sequence?->last_number ?? 0;
    }

    public function resetSequence(int $businessId, DocumentType $documentType, int $startNumber = 0): void
    {
        $year = now()->year;

        DocumentSequenceModel::updateOrCreate(
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
