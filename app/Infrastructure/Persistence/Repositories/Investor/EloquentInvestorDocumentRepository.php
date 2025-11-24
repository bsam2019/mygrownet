<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\InvestorDocument;
use App\Domain\Investor\Repositories\InvestorDocumentRepositoryInterface;
use App\Domain\Investor\ValueObjects\DocumentCategory;
use App\Domain\Investor\ValueObjects\DocumentStatus;
use App\Infrastructure\Persistence\Eloquent\Investor\InvestorDocumentModel;
use App\Infrastructure\Persistence\Eloquent\Investor\InvestorDocumentAccessModel;

class EloquentInvestorDocumentRepository implements InvestorDocumentRepositoryInterface
{
    public function findById(int $id): ?InvestorDocument
    {
        $model = InvestorDocumentModel::find($id);
        
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCategory(DocumentCategory $category): array
    {
        $models = InvestorDocumentModel::byCategory($category->value())
            ->active()
            ->orderBy('upload_date', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findVisibleToInvestor(int $investmentRoundId): array
    {
        $models = InvestorDocumentModel::forInvestmentRound($investmentRoundId)
            ->active()
            ->orderBy('category')
            ->orderBy('upload_date', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findAll(): array
    {
        $models = InvestorDocumentModel::orderBy('upload_date', 'desc')->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findByStatus(DocumentStatus $status): array
    {
        $models = InvestorDocumentModel::where('status', $status->value)
            ->orderBy('upload_date', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function save(InvestorDocument $document): InvestorDocument
    {
        $data = [
            'title' => $document->getTitle(),
            'description' => $document->getDescription(),
            'category' => $document->getCategory()->value(),
            'file_path' => $document->getFilePath(),
            'file_name' => $document->getFileName(),
            'file_size' => $document->getFileSize(),
            'mime_type' => $document->getMimeType(),
            'upload_date' => $document->getUploadDate(),
            'uploaded_by' => $document->getUploadedBy(),
            'visible_to_all' => $document->isVisibleToAll(),
            'investment_round_id' => $document->getInvestmentRoundId(),
            'status' => $document->getStatus()->value,
            'download_count' => $document->getDownloadCount(),
        ];

        if ($document->getId() === 0) {
            // Create new document
            $model = InvestorDocumentModel::create($data);
        } else {
            // Update existing document
            $model = InvestorDocumentModel::findOrFail($document->getId());
            $model->update($data);
        }

        return $this->toDomainEntity($model);
    }

    public function delete(int $id): void
    {
        InvestorDocumentModel::destroy($id);
    }

    public function incrementDownloadCount(int $id): void
    {
        InvestorDocumentModel::where('id', $id)->increment('download_count');
    }

    public function logAccess(int $investorAccountId, int $documentId, string $ipAddress, string $userAgent): void
    {
        InvestorDocumentAccessModel::create([
            'investor_account_id' => $investorAccountId,
            'investor_document_id' => $documentId,
            'accessed_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    public function getAccessLog(int $documentId): array
    {
        return InvestorDocumentAccessModel::where('investor_document_id', $documentId)
            ->with('investorAccount')
            ->orderBy('accessed_at', 'desc')
            ->get()
            ->toArray();
    }

    public function getInvestorAccessHistory(int $investorAccountId): array
    {
        return InvestorDocumentAccessModel::where('investor_account_id', $investorAccountId)
            ->with('document')
            ->orderBy('accessed_at', 'desc')
            ->get()
            ->toArray();
    }

    public function getTotalDocuments(): int
    {
        return InvestorDocumentModel::active()->count();
    }

    public function getTotalDownloads(): int
    {
        return InvestorDocumentModel::active()->sum('download_count');
    }

    public function getMostDownloadedDocuments(int $limit = 10): array
    {
        $models = InvestorDocumentModel::active()
            ->orderBy('download_count', 'desc')
            ->limit($limit)
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    private function toDomainEntity(InvestorDocumentModel $model): InvestorDocument
    {
        return new InvestorDocument(
            id: $model->id,
            title: $model->title,
            description: $model->description ?? '',
            category: DocumentCategory::fromString($model->category),
            filePath: $model->file_path,
            fileName: $model->file_name,
            fileSize: $model->file_size,
            mimeType: $model->mime_type,
            uploadDate: $model->upload_date->toDateTimeImmutable(),
            uploadedBy: $model->uploaded_by,
            visibleToAll: $model->visible_to_all,
            investmentRoundId: $model->investment_round_id,
            status: DocumentStatus::from($model->status),
            downloadCount: $model->download_count
        );
    }
}