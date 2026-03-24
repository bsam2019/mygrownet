<?php

namespace App\Application\BizDocs\DTOs;

class CancelDocumentDTO
{
    public function __construct(
        public readonly int $documentId,
        public readonly string $reason
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            documentId: $data['document_id'],
            reason: $data['reason']
        );
    }
}
