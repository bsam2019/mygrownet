<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\Entities;

use App\Domain\QuickInvoice\Entities\Document;
use App\Domain\QuickInvoice\Entities\LineItem;
use App\Domain\QuickInvoice\ValueObjects\BusinessInfo;
use App\Domain\QuickInvoice\ValueObjects\ClientInfo;
use App\Domain\QuickInvoice\ValueObjects\DocumentId;
use App\Domain\QuickInvoice\ValueObjects\DocumentNumber;
use App\Domain\QuickInvoice\ValueObjects\DocumentType;
use App\Domain\QuickInvoice\ValueObjects\ThemeColors;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

const TEST_SESSION = 'test-session';

final class DocumentTest extends TestCase
{
    private BusinessInfo $business;
    private ClientInfo $client;

    protected function setUp(): void
    {
        $this->business = BusinessInfo::create('Acme Ltd', '123 Main St', '+260977123456', 'info@acme.com');
        $this->client = ClientInfo::create('John Doe', '456 Oak Ave', null, 'john@example.com');
    }

    #[Test]
    public function create_sets_required_fields(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, sessionId: TEST_SESSION);
        $this->assertSame('draft', $doc->status());
        $this->assertEquals(DocumentType::INVOICE, $doc->type());
        $this->assertSame('ZMW', $doc->currency());
        $this->assertNull($doc->userId());
        $this->assertNotNull($doc->id()->value());
        $this->assertNotNull($doc->documentNumber()->value());
    }

    #[Test]
    public function create_sets_issue_date_to_today(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, sessionId: TEST_SESSION);
        $this->assertEquals(Carbon::today()->format('Y-m-d'), $doc->issueDate()->format('Y-m-d'));
    }

    #[Test]
    public function create_sets_due_date_for_invoice(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, sessionId: TEST_SESSION);
        $this->assertEquals(Carbon::today()->addDays(30)->format('Y-m-d'), $doc->dueDate()->format('Y-m-d'));
    }

    #[Test]
    public function create_no_due_date_for_delivery_note(): void
    {
        $doc = Document::create(DocumentType::DELIVERY_NOTE, $this->business, $this->client, sessionId: TEST_SESSION);
        $this->assertNull($doc->dueDate());
    }

    #[Test]
    public function create_with_user_id_and_session(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, 'USD', 42, 'sess-1');
        $this->assertSame(42, $doc->userId());
        $this->assertSame('sess-1', $doc->sessionId());
    }

    #[Test]
    public function create_with_template_and_colors(): void
    {
        $colors = ThemeColors::create('#ff0000');
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, 'ZMW', null, TEST_SESSION, 'modern', $colors);
        $this->assertSame('modern', $doc->template());
        $this->assertSame('#ff0000', $doc->colors()->primary());
    }

    #[Test]
    public function create_defaults_template_to_classic(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, sessionId: TEST_SESSION);
        $this->assertSame('classic', $doc->template());
    }

    #[Test]
    public function create_initial_totals_are_zero(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, sessionId: TEST_SESSION);
        $this->assertSame(0.0, $doc->subtotal()->amount());
        $this->assertSame(0.0, $doc->taxAmount()->amount());
        $this->assertSame(0.0, $doc->discountAmount()->amount());
        $this->assertSame(0.0, $doc->total()->amount());
    }

    #[Test]
    public function add_item_recalculates_total(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, sessionId: TEST_SESSION);
        $doc->setTaxRate(16);
        $item = LineItem::create('Product', 2, 100);
        $doc->addItem($item);
        $this->assertSame(200.0, $doc->subtotal()->amount());
        $this->assertSame(32.0, $doc->taxAmount()->amount());
        $this->assertSame(232.0, $doc->total()->amount());
    }

    #[Test]
    public function remove_item_updates_total(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, sessionId: TEST_SESSION);
        $item1 = LineItem::create('Product A', 1, 100);
        $item2 = LineItem::create('Product B', 1, 50);
        $doc->addItem($item1);
        $doc->addItem($item2);
        $this->assertSame(150.0, $doc->subtotal()->amount());
        $doc->removeItem($item1->id());
        $this->assertSame(50.0, $doc->subtotal()->amount());
    }

    #[Test]
    public function set_items_from_arrays(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, sessionId: TEST_SESSION);
        $doc->setItems([
            ['description' => 'Item 1', 'quantity' => 3, 'unit_price' => 20],
            ['description' => 'Item 2', 'quantity' => 1, 'unit_price' => 50],
        ]);
        $this->assertCount(2, $doc->items());
        $this->assertSame(110.0, $doc->subtotal()->amount());
        $this->assertSame(0, $doc->items()[0]->sortOrder());
        $this->assertSame(1, $doc->items()[1]->sortOrder());
    }

    #[Test]
    public function set_items_from_line_items(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, sessionId: TEST_SESSION);
        $doc->setItems([LineItem::create('Direct', 5, 10)]);
        $this->assertCount(1, $doc->items());
        $this->assertSame(50.0, $doc->subtotal()->amount());
    }

    #[Test]
    public function set_tax_rate_clamps_between_0_and_100(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, sessionId: TEST_SESSION);
        $doc->setTaxRate(150);
        $this->assertSame(100.0, $doc->taxRate());
        $doc->setTaxRate(-10);
        $this->assertSame(0.0, $doc->taxRate());
    }

    #[Test]
    public function set_discount_rate_clamps_between_0_and_100(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, sessionId: TEST_SESSION);
        $doc->addItem(LineItem::create('Item', 1, 200));
        $doc->setDiscountRate(200);
        $this->assertSame(100.0, $doc->discountRate());
        $this->assertSame(200.0, $doc->discountAmount()->amount());
    }

    #[Test]
    public function discount_applied_before_tax(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, sessionId: TEST_SESSION);
        $doc->addItem(LineItem::create('Item', 1, 1000));
        $doc->setDiscountRate(10);
        $doc->setTaxRate(16);
        $this->assertSame(1000.0, $doc->subtotal()->amount());
        $this->assertSame(100.0, $doc->discountAmount()->amount());
        $this->assertSame(144.0, $doc->taxAmount()->amount());
        $this->assertSame(1044.0, $doc->total()->amount());
    }

    #[Test]
    public function status_transitions(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, sessionId: TEST_SESSION);
        $this->assertSame('draft', $doc->status());
        $doc->markAsSent();
        $this->assertSame('sent', $doc->status());
        $doc->markAsPaid();
        $this->assertSame('paid', $doc->status());
        $doc->cancel();
        $this->assertSame('cancelled', $doc->status());
    }

    #[Test]
    public function can_be_accessed_by_user_id(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, 'ZMW', 5, TEST_SESSION);
        $this->assertTrue($doc->canBeAccessedBy(5, null));
        $this->assertFalse($doc->canBeAccessedBy(99, null));
    }

    #[Test]
    public function can_be_accessed_by_session_id(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, 'ZMW', null, 'sess-guest');
        $this->assertTrue($doc->canBeAccessedBy(null, 'sess-guest'));
        $this->assertFalse($doc->canBeAccessedBy(null, 'sess-other'));
    }

    #[Test]
    public function can_be_accessed_by_either(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, 'ZMW', 10, 'sess-1');
        $this->assertTrue($doc->canBeAccessedBy(10, 'wrong-sess'));
        $this->assertTrue($doc->canBeAccessedBy(null, 'sess-1'));
        $this->assertFalse($doc->canBeAccessedBy(99, 'wrong-sess'));
    }

    #[Test]
    public function reconstitute_restores_full_state(): void
    {
        $id = DocumentId::fromString('restored-id');
        $num = DocumentNumber::fromString('INV-001');
        $items = [LineItem::create('Restored', 2, 25)];
        $now = Carbon::now();
        $colors = ThemeColors::create('#ff0000', '#cc0000', '#00ff00', '#111111', '#ffffff');

        $doc = Document::reconstitute(
            id: $id,
            userId: 99,
            sessionId: 'sess-r',
            type: DocumentType::QUOTATION,
            documentNumber: $num,
            businessInfo: $this->business,
            clientInfo: $this->client,
            issueDate: $now,
            dueDate: null,
            currency: 'USD',
            items: $items,
            taxRate: 10,
            discountRate: 5,
            notes: 'Note',
            terms: 'Terms',
            status: 'sent',
            createdAt: $now,
            updatedAt: $now,
            template: 'bold',
            colors: $colors,
            signature: 'sig.png',
            preparedBy: 'Alice',
            attachments: ['file1.pdf']
        );

        $this->assertSame('restored-id', $doc->id()->value());
        $this->assertSame(99, $doc->userId());
        $this->assertEquals(DocumentType::QUOTATION, $doc->type());
        $this->assertSame('INV-001', $doc->documentNumber()->value());
        $this->assertSame('USD', $doc->currency());
        $this->assertSame(10.0, $doc->taxRate());
        $this->assertSame(5.0, $doc->discountRate());
        $this->assertSame('Note', $doc->notes());
        $this->assertSame('Terms', $doc->terms());
        $this->assertSame('sent', $doc->status());
        $this->assertSame('bold', $doc->template());
        $this->assertSame('#ff0000', $doc->colors()->primary());
        $this->assertSame('sig.png', $doc->signature());
        $this->assertSame('Alice', $doc->preparedBy());
        $this->assertSame(['file1.pdf'], $doc->attachments());
    }

    #[Test]
    public function reconstitute_recalculates(): void
    {
        $items = [LineItem::create('Item', 3, 100)];
        $doc = Document::reconstitute(
            id: DocumentId::generate(),
            userId: null,
            sessionId: 'sess',
            type: DocumentType::INVOICE,
            documentNumber: DocumentNumber::generate(DocumentType::INVOICE),
            businessInfo: $this->business,
            clientInfo: $this->client,
            issueDate: Carbon::today(),
            dueDate: Carbon::today()->addDays(30),
            currency: 'ZMW',
            items: $items,
            taxRate: 16,
            discountRate: 0,
            notes: null,
            terms: null,
            status: 'draft',
            createdAt: Carbon::now(),
            updatedAt: Carbon::now()
        );
        $this->assertSame(300.0, $doc->subtotal()->amount());
        $this->assertSame(48.0, $doc->taxAmount()->amount());
        $this->assertSame(348.0, $doc->total()->amount());
    }

    #[Test]
    public function to_array_returns_full_structure(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, 'ZMW', 1, 'sess-1', 'modern');
        $doc->addItem(LineItem::create('Test', 1, 100));
        $doc->setTaxRate(16);
        $doc->setNotes('Thank you');
        $doc->setTerms('Net 30');
        $doc->setSignature('sig.png');
        $doc->setPreparedBy('Alice');

        $arr = $doc->toArray();
        $this->assertSame('invoice', $arr['type']);
        $this->assertSame('Invoice', $arr['type_label']);
        $this->assertSame(1, $arr['user_id']);
        $this->assertSame('modern', $arr['template']);
        $this->assertSame(100.0, $arr['subtotal']);
        $this->assertSame(16.0, $arr['tax_amount']);
        $this->assertSame(0.0, $arr['discount_amount']);
        $this->assertSame(116.0, $arr['total']);
        $this->assertSame('Thank you', $arr['notes']);
        $this->assertSame('Net 30', $arr['terms']);
        $this->assertSame('draft', $arr['status']);
        $this->assertSame('sig.png', $arr['signature']);
        $this->assertSame('Alice', $arr['prepared_by']);
        $this->assertCount(1, $arr['items']);
        $this->assertIsString($arr['created_at']);
        $this->assertIsString($arr['updated_at']);
    }

    #[Test]
    public function set_notes_and_terms(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, sessionId: TEST_SESSION);
        $doc->setNotes('Payment expected within 30 days');
        $this->assertSame('Payment expected within 30 days', $doc->notes());
        $doc->setTerms('Full payment upon receipt');
        $this->assertSame('Full payment upon receipt', $doc->terms());
    }

    #[Test]
    public function set_due_date_and_issue_date(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, sessionId: TEST_SESSION);
        $newIssue = Carbon::yesterday();
        $newDue = Carbon::today()->addDays(15);
        $doc->setIssueDate($newIssue);
        $doc->setDueDate($newDue);
        $this->assertEquals($newIssue->format('Y-m-d'), $doc->issueDate()->format('Y-m-d'));
        $this->assertEquals($newDue->format('Y-m-d'), $doc->dueDate()->format('Y-m-d'));
    }

    #[Test]
    public function set_document_number(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, sessionId: TEST_SESSION);
        $newNum = DocumentNumber::fromString('CUSTOM-001');
        $doc->setDocumentNumber($newNum);
        $this->assertSame('CUSTOM-001', $doc->documentNumber()->value());
    }

    #[Test]
    public function set_colors(): void
    {
        $doc = Document::create(DocumentType::INVOICE, $this->business, $this->client, sessionId: TEST_SESSION);
        $new = ThemeColors::create('#00ff00');
        $doc->setColors($new);
        $this->assertSame('#00ff00', $doc->colors()->primary());
    }
}
