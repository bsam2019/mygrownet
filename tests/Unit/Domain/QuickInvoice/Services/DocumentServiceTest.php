<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\Services;

use App\Domain\QuickInvoice\Entities\Document;
use App\Domain\QuickInvoice\Exceptions\DocumentNotFoundException;
use App\Domain\QuickInvoice\Exceptions\UnauthorizedAccessException;
use App\Domain\QuickInvoice\Repositories\DocumentRepositoryInterface;
use App\Domain\QuickInvoice\Services\DocumentService;
use App\Domain\QuickInvoice\ValueObjects\BusinessInfo;
use App\Domain\QuickInvoice\ValueObjects\ClientInfo;
use App\Domain\QuickInvoice\ValueObjects\DocumentId;
use App\Domain\QuickInvoice\ValueObjects\DocumentNumber;
use App\Domain\QuickInvoice\ValueObjects\DocumentType;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class DocumentServiceTest extends TestCase
{
    private DocumentRepositoryInterface&\PHPUnit\Framework\MockObject\Stub $repository;
    private DocumentService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createStub(DocumentRepositoryInterface::class);
        $this->service = new DocumentService($this->repository);
    }

    private function createMinimalDocument(int $userId = null, string $sessionId = 'test'): Document
    {
        return Document::reconstitute(
            id: DocumentId::generate(),
            userId: $userId,
            sessionId: $sessionId,
            type: DocumentType::INVOICE,
            documentNumber: DocumentNumber::generate(DocumentType::INVOICE),
            businessInfo: BusinessInfo::create('Biz'),
            clientInfo: ClientInfo::create('Client'),
            issueDate: Carbon::today(),
            dueDate: null,
            currency: 'ZMW',
            items: [],
            taxRate: 0,
            discountRate: 0,
            notes: null,
            terms: null,
            status: 'draft',
            createdAt: Carbon::now(),
            updatedAt: Carbon::now(),
        );
    }

    #[Test]
    public function find_document_returns_document(): void
    {
        $document = $this->createMinimalDocument();
        $this->repository
            ->method('findById')
            ->willReturnCallback(fn($arg) => $arg->value() === $document->id()->value() ? $document : null);

        $result = $this->service->findDocument($document->id()->value());
        $this->assertNotNull($result);
    }

    #[Test]
    public function find_document_not_found_throws(): void
    {
        $this->repository
            ->method('findById')
            ->willReturn(null);

        $this->expectException(DocumentNotFoundException::class);
        $this->service->findDocument('non-existent');
    }

    #[Test]
    public function find_document_with_access_allows_owner(): void
    {
        $document = $this->createMinimalDocument(userId: 42);
        $this->repository
            ->method('findById')
            ->willReturn($document);

        $result = $this->service->findDocumentWithAccess($document->id()->value(), 42, null);
        $this->assertSame($document, $result);
    }

    #[Test]
    public function find_document_with_access_denies_non_owner(): void
    {
        $document = $this->createMinimalDocument(userId: 42);
        $this->repository
            ->method('findById')
            ->willReturn($document);

        $this->expectException(UnauthorizedAccessException::class);
        $this->service->findDocumentWithAccess($document->id()->value(), 99, null);
    }

    #[Test]
    public function get_documents_by_session_delegates(): void
    {
        $this->repository
            ->method('findBySessionId')
            ->willReturn([]);

        $this->assertSame([], $this->service->getDocumentsBySession('sess-1'));
    }

    #[Test]
    public function get_documents_by_user_delegates(): void
    {
        $this->repository
            ->method('findByUserId')
            ->willReturn([]);

        $this->assertSame([], $this->service->getDocumentsByUser(1));
    }

    #[Test]
    public function get_recent_documents_by_user_delegates(): void
    {
        $this->repository
            ->method('findRecentByUser')
            ->willReturn([]);

        $this->assertSame([], $this->service->getRecentDocumentsByUser(1, 3));
    }

    #[Test]
    public function get_total_document_count_delegates(): void
    {
        $this->repository
            ->method('countAll')
            ->willReturn(42);

        $this->assertSame(42, $this->service->getTotalDocumentCount());
    }

    #[Test]
    public function delete_document_unauthorized_throws(): void
    {
        $document = $this->createMinimalDocument(userId: 10);
        $this->repository
            ->method('findById')
            ->willReturn($document);

        $this->expectException(UnauthorizedAccessException::class);
        $this->service->deleteDocument($document->id()->value(), 99, null);
    }

    #[Test]
    public function delete_document_not_found_throws(): void
    {
        $this->repository
            ->method('findById')
            ->willReturn(null);

        $this->expectException(DocumentNotFoundException::class);
        $this->service->deleteDocument('ghost', 1, null);
    }
}
