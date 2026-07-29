<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\Services;

use App\Domain\QuickInvoice\Entities\Document;
use App\Domain\QuickInvoice\Services\PdfGeneratorService;
use App\Domain\QuickInvoice\Services\ShareService;
use App\Domain\QuickInvoice\ValueObjects\BusinessInfo;
use App\Domain\QuickInvoice\ValueObjects\ClientInfo;
use App\Domain\QuickInvoice\ValueObjects\DocumentType;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class ShareServiceTest extends TestCase
{
    private PdfGeneratorService&\PHPUnit\Framework\MockObject\Stub $pdfGenerator;

    private ShareService $service;

    protected function setUp(): void
    {
        $this->pdfGenerator = $this->createStub(PdfGeneratorService::class);
        $this->service = new ShareService($this->pdfGenerator);
    }

    private function createTestDocument(?string $phone = '+260977123456', ?string $email = 'client@test.com'): Document
    {
        return Document::create(
            DocumentType::INVOICE,
            BusinessInfo::create('Acme Ltd', phone: '+260977111222'),
            ClientInfo::create('John Doe', phone: $phone, email: $email),
            'ZMW',
            1,
            'sess-1',
        );
    }

    #[Test]
    public function generate_whatsapp_link_contains_wa_me_url(): void
    {
        $doc = $this->createTestDocument();
        $link = $this->service->generateWhatsAppLink($doc, 'https://example.com/invoice.pdf');
        $this->assertStringStartsWith('https://wa.me/', $link);
    }

    #[Test]
    public function generate_whatsapp_link_contains_phone(): void
    {
        $doc = $this->createTestDocument('+260977123456');
        $link = $this->service->generateWhatsAppLink($doc, 'https://example.com/invoice.pdf');
        $this->assertStringContainsString('260977123456', $link);
    }

    #[Test]
    public function generate_whatsapp_link_adds_260_prefix_for_9_digit(): void
    {
        $doc = $this->createTestDocument('977123456');
        $link = $this->service->generateWhatsAppLink($doc, 'https://example.com/invoice.pdf');
        $this->assertStringContainsString('260977123456', $link);
    }

    #[Test]
    public function generate_whatsapp_link_replaces_leading_zero(): void
    {
        $doc = $this->createTestDocument('0977123456');
        $link = $this->service->generateWhatsAppLink($doc, 'https://example.com/invoice.pdf');
        $this->assertStringContainsString('260977123456', $link);
    }

    #[Test]
    public function generate_whatsapp_link_strips_non_numeric(): void
    {
        $doc = $this->createTestDocument('+260 977 123 456');
        $link = $this->service->generateWhatsAppLink($doc, 'https://example.com/invoice.pdf');
        $this->assertStringContainsString('260977123456', $link);
    }

    #[Test]
    public function generate_whatsapp_link_contains_pdf_url(): void
    {
        $doc = $this->createTestDocument();
        $link = $this->service->generateWhatsAppLink($doc, 'https://dl.example.com/inv.pdf');
        $this->assertStringContainsString(urlencode('https://dl.example.com/inv.pdf'), $link);
    }
}
