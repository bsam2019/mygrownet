<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Services;

use App\Domain\QuickInvoice\Entities\Document;
use App\Domain\QuickInvoice\Exceptions\DocumentNotFoundException;
use App\Domain\QuickInvoice\Exceptions\InvalidDocumentDataException;
use App\Domain\QuickInvoice\Exceptions\UnauthorizedAccessException;
use App\Domain\QuickInvoice\Repositories\DocumentRepositoryInterface;
use App\Domain\QuickInvoice\ValueObjects\BusinessInfo;
use App\Domain\QuickInvoice\ValueObjects\ClientInfo;
use App\Domain\QuickInvoice\ValueObjects\DocumentId;
use App\Domain\QuickInvoice\ValueObjects\DocumentNumber;
use App\Domain\QuickInvoice\ValueObjects\DocumentType;
use App\Domain\QuickInvoice\ValueObjects\TemplateStyle;
use App\Domain\QuickInvoice\ValueObjects\ThemeColors;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DocumentService
{
    public function __construct(
        private readonly DocumentRepositoryInterface $documentRepository
    ) {}

    /**
     * Create a new document from input data
     * 
     * @throws InvalidDocumentDataException
     */
    public function createDocument(array $data): Document
    {
        try {
            $type = DocumentType::from($data['document_type']);
            
            $businessInfo = BusinessInfo::create(
                name: $data['business_name'],
                address: $data['business_address'] ?? null,
                phone: $data['business_phone'] ?? null,
                email: $data['business_email'] ?? null,
                logo: $data['business_logo'] ?? null,
                taxNumber: $data['business_tax_number'] ?? null,
                website: $data['business_website'] ?? null
            );

            $clientInfo = ClientInfo::create(
                name: $data['client_name'],
                address: $data['client_address'] ?? null,
                phone: $data['client_phone'] ?? null,
                email: $data['client_email'] ?? null
            );

            $template = isset($data['template']) 
                ? TemplateStyle::from($data['template']) 
                : TemplateStyle::CLASSIC;

            $colors = isset($data['colors']) 
                ? ThemeColors::fromArray($data['colors']) 
                : ThemeColors::default();

            $document = Document::create(
                type: $type,
                businessInfo: $businessInfo,
                clientInfo: $clientInfo,
                currency: $data['currency'] ?? 'ZMW',
                userId: $data['user_id'] ?? null,
                sessionId: $data['session_id'] ?? null,
                template: $template,
                colors: $colors
            );

            $this->applyOptionalFields($document, $data);

            if (!empty($data['items'])) {
                $document->setItems($data['items']);
            }

            Log::info('Document created', [
                'document_id' => $document->id()->value(),
                'type' => $type->value,
                'user_id' => $data['user_id'] ?? null,
            ]);

            return $document;
        } catch (Throwable $e) {
            Log::error('Failed to create document', [
                'error' => $e->getMessage(),
                'data' => array_keys($data),
            ]);
            throw new InvalidDocumentDataException($e->getMessage());
        }
    }

    /**
     * Save a document to persistence
     */
    public function saveDocument(Document $document): Document
    {
        $savedDocument = $this->documentRepository->save($document);
        
        Log::info('Document saved', [
            'document_id' => $savedDocument->id()->value(),
        ]);

        return $savedDocument;
    }

    /**
     * Find a document by ID
     * 
     * @throws DocumentNotFoundException
     */
    public function findDocument(string $id): Document
    {
        $document = $this->documentRepository->findById(DocumentId::fromString($id));
        
        if (!$document) {
            throw new DocumentNotFoundException($id);
        }

        return $document;
    }

    /**
     * Find a document and verify access
     * 
     * @throws DocumentNotFoundException
     * @throws UnauthorizedAccessException
     */
    public function findDocumentWithAccess(string $id, ?int $userId, ?string $sessionId): Document
    {
        $document = $this->findDocument($id);
        
        if (!$document->canBeAccessedBy($userId, $sessionId)) {
            throw new UnauthorizedAccessException($id);
        }

        return $document;
    }

    /**
     * Get documents for a session (guest users)
     */
    public function getDocumentsBySession(string $sessionId): array
    {
        return $this->documentRepository->findBySessionId($sessionId);
    }

    /**
     * Get documents for a user
     */
    public function getDocumentsByUser(int $userId): array
    {
        return $this->documentRepository->findByUserId($userId);
    }

    /**
     * Delete a document
     * 
     * @throws DocumentNotFoundException
     * @throws UnauthorizedAccessException
     */
    public function deleteDocument(string $id, ?int $userId, ?string $sessionId): bool
    {
        $document = $this->findDocumentWithAccess($id, $userId, $sessionId);
        
        $deleted = $this->documentRepository->delete($document->id());
        
        if ($deleted) {
            Log::info('Document deleted', ['document_id' => $id]);
        }

        return $deleted;
    }

    /**
     * Upload a logo file
     */
    public function uploadLogo($file, ?string $sessionId = null): string
    {
        $sessionId = $sessionId ?? session()->getId();
        $filename = 'logo_' . $sessionId . '_' . time() . '.' . $file->getClientOriginalExtension();
        
        $path = $file->storeAs('quick-invoice/logos', $filename, 'public');
        
        return Storage::disk('public')->url($path);
    }

    /**
     * Apply optional fields to a document
     */
    private function applyOptionalFields(Document $document, array $data): void
    {
        if (!empty($data['document_number'])) {
            $document->setDocumentNumber(DocumentNumber::fromString($data['document_number']));
        }

        if (!empty($data['issue_date'])) {
            $document->setIssueDate(Carbon::parse($data['issue_date']));
        }

        if (!empty($data['due_date'])) {
            $document->setDueDate(Carbon::parse($data['due_date']));
        }

        if (isset($data['tax_rate'])) {
            $document->setTaxRate((float) $data['tax_rate']);
        }

        if (isset($data['discount_rate'])) {
            $document->setDiscountRate((float) $data['discount_rate']);
        }

        if (!empty($data['notes'])) {
            $document->setNotes($data['notes']);
        }

        if (!empty($data['terms'])) {
            $document->setTerms($data['terms']);
        }

        // Template & Styling
        if (!empty($data['template'])) {
            $document->setTemplate(TemplateStyle::from($data['template']));
        }

        if (!empty($data['colors'])) {
            $document->setColors(ThemeColors::fromArray($data['colors']));
        }

        if (!empty($data['signature'])) {
            $document->setSignature($data['signature']);
        }
        
        if (!empty($data['prepared_by'])) {
            $document->setPreparedBy($data['prepared_by']);
        }
    }

    /**
     * Upload a signature file
     */
    public function uploadSignature($file, ?string $sessionId = null): string
    {
        $sessionId = $sessionId ?? session()->getId();
        $filename = 'signature_' . $sessionId . '_' . time() . '.' . $file->getClientOriginalExtension();
        
        $path = $file->storeAs('quick-invoice/signatures', $filename, 'public');
        
        return Storage::disk('public')->url($path);
    }
}
