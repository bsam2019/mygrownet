<?php

namespace App\Application\BizDocs\DTOs;

class GenerateStationeryDTO
{
    public function __construct(
        public readonly int $businessId,
        public readonly string $documentType,
        public readonly int $templateId,
        public readonly int $quantity,
        public readonly int $documentsPerPage,
        public readonly string $startingNumber,
        public readonly string $pageSize = 'A4',
        public readonly ?int $rowCount = null,
    ) {}
}
