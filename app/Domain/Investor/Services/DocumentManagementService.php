<?php

namespace App\Domain\Investor\Services;

use App\Domain\Investor\Entities\InvestorDocument;
use App\Domain\Investor\Repositories\InvestorDocumentRepositoryInterface;
use App\Domain\Investor\ValueObjects\DocumentCategory;
use App\Domain\Investor\ValueObjects\DocumentStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentManagementService
{
    public function __construct(
        private InvestorDocumentRepositoryInterface $documentRepository
    ) {}

    public function uploadDocument(
        UploadedFile $file,
        string $title,
        string $description,
        string $category,
        int $uploadedBy,
        bool $visibleToAll = true,
        ?int $investmentRoundId = null
    ): InvestorDocument {
        // Validate file
        $this->validateFile($file);

        // Generate unique filename
        $fileName = $this->generateUniqueFileName($file);
        
        // Store file in private storage
        $filePath = $file->storeAs('investor-documents', $fileName, 'private');

        // Create document entity
        $document = new InvestorDocument(
            id: 0, // Will be set by repository
            title: $title,
            description: $description,
            category: DocumentCategory::fromString($category),
            filePath: $filePath,
            fileName: $file->getClientOriginalName(),
            fileSize: $file->getSize(),
            mimeType: $file->getMimeType(),
            uploadDate: new \DateTimeImmutable(),
            uploadedBy: $uploadedBy,
            visibleToAll: $visibleToAll,
            investmentRoundId: $investmentRoundId,
            status: DocumentStatus::ACTIVE
        );

        return $this->documentRepository->save($document);
    }

    public function getDocumentsForInvestor(int $investmentRoundId): array
    {
        $documents = $this->documentRepository->findVisibleToInvestor($investmentRoundId);
        
        // Group by category
        $grouped = [];
        foreach ($documents as $document) {
            $category = $document->getCategory()->value();
            if (!isset($grouped[$category])) {
                $grouped[$category] = [
                    'category' => [
                        'value' => $category,
                        'label' => $document->getCategory()->label(),
                        'icon' => $document->getCategory()->icon(),
                        'description' => $document->getCategory()->description(),
                    ],
                    'documents' => []
                ];
            }
            $grouped[$category]['documents'][] = $document->toArray();
        }

        return $grouped;
    }

    public function getAllDocumentsGrouped(): array
    {
        $documents = $this->documentRepository->findByStatus(DocumentStatus::ACTIVE);
        
        // Group by category
        $grouped = [];
        foreach ($documents as $document) {
            $category = $document->getCategory()->value();
            if (!isset($grouped[$category])) {
                $grouped[$category] = [
                    'category' => [
                        'value' => $category,
                        'label' => $document->getCategory()->label(),
                        'icon' => $document->getCategory()->icon(),
                        'description' => $document->getCategory()->description(),
                    ],
                    'documents' => []
                ];
            }
            $grouped[$category]['documents'][] = $document->toArray();
        }

        return $grouped;
    }

    public function downloadDocument(int $documentId, int $investorAccountId, string $ipAddress, string $userAgent): array
    {
        $document = $this->documentRepository->findById($documentId);
        
        if (!$document) {
            throw new \Exception('Document not found');
        }

        if (!$document->getStatus()->isActive()) {
            throw new \Exception('Document is not available');
        }

        // Log access
        $this->documentRepository->logAccess($investorAccountId, $documentId, $ipAddress, $userAgent);
        
        // Increment download count
        $this->documentRepository->incrementDownloadCount($documentId);

        // Get file path
        $fullPath = Storage::disk('private')->path($document->getFilePath());
        
        if (!file_exists($fullPath)) {
            throw new \Exception('File not found on disk');
        }

        return [
            'path' => $fullPath,
            'name' => $document->getFileName(),
            'mime_type' => $document->getMimeType(),
            'size' => $document->getFileSize(),
        ];
    }

    public function updateDocument(
        int $documentId,
        string $title,
        string $description,
        bool $visibleToAll,
        ?int $investmentRoundId = null
    ): InvestorDocument {
        $document = $this->documentRepository->findById($documentId);
        
        if (!$document) {
            throw new \Exception('Document not found');
        }

        $document->updateTitle($title);
        $document->updateDescription($description);
        $document->updateVisibility($visibleToAll, $investmentRoundId);

        return $this->documentRepository->save($document);
    }

    public function deleteDocument(int $documentId): void
    {
        $document = $this->documentRepository->findById($documentId);
        
        if (!$document) {
            throw new \Exception('Document not found');
        }

        // Delete file from storage
        Storage::disk('private')->delete($document->getFilePath());

        // Delete from database
        $this->documentRepository->delete($documentId);
    }

    public function archiveDocument(int $documentId): InvestorDocument
    {
        $document = $this->documentRepository->findById($documentId);
        
        if (!$document) {
            throw new \Exception('Document not found');
        }

        $document->archive();
        return $this->documentRepository->save($document);
    }

    public function getDocumentStats(): array
    {
        return [
            'total_documents' => $this->documentRepository->getTotalDocuments(),
            'total_downloads' => $this->documentRepository->getTotalDownloads(),
            'most_downloaded' => $this->documentRepository->getMostDownloadedDocuments(5),
        ];
    }

    public function getAvailableCategories(): array
    {
        return DocumentCategory::getAllWithDetails();
    }

    public function getDocumentById(int $documentId): ?InvestorDocument
    {
        return $this->documentRepository->findById($documentId);
    }

    public function getDocumentAccessLogs(int $documentId): array
    {
        return $this->documentRepository->getAccessLog($documentId);
    }

    private function validateFile(UploadedFile $file): void
    {
        $allowedMimeTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
            'image/gif',
            'text/plain',
        ];

        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            throw new \Exception('File type not allowed. Allowed types: PDF, Word, Excel, Images, Text files.');
        }

        // 10MB limit
        if ($file->getSize() > 10 * 1024 * 1024) {
            throw new \Exception('File size too large. Maximum size is 10MB.');
        }

        // Check for malicious files
        $dangerousExtensions = ['exe', 'bat', 'cmd', 'scr', 'pif', 'vbs', 'js', 'jar'];
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (in_array($extension, $dangerousExtensions)) {
            throw new \Exception('File type not allowed for security reasons.');
        }
    }

    private function generateUniqueFileName(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = time();
        $random = uniqid();
        
        return "{$timestamp}_{$random}.{$extension}";
    }
}