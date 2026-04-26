<?php

namespace App\Domain\CMS\BizDocs\Adapters;

use App\Application\BizDocs\Services\PdfGenerationService;
use App\Application\BizDocs\Services\FileStorageService;
use App\Domain\BizDocs\BusinessIdentity\Entities\BusinessProfile;
use App\Domain\BizDocs\CustomerManagement\Entities\Customer;
use App\Domain\BizDocs\DocumentManagement\Entities\Document;
use App\Domain\BizDocs\DocumentManagement\Entities\DocumentItem;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentNumber;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\DocumentType;
use App\Domain\BizDocs\DocumentManagement\ValueObjects\Money;
use App\Domain\CMS\BizDocs\Contracts\DocumentGeneratorInterface;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\QuotationModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PaymentModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;

class BizDocsAdapter implements DocumentGeneratorInterface
{
    public function __construct(
        private PdfGenerationService $pdfService,
        private FileStorageService $fileStorageService
    ) {}

    /**
     * Generate PDF for an invoice using BizDocs PdfGenerationService directly.
     */
    public function generateInvoicePdf(InvoiceModel $invoice): string
    {
        $invoice->loadMissing(['company', 'customer', 'items']);

        $businessProfile = $this->buildBusinessProfile($invoice->company);
        $customer        = $this->buildCustomer($invoice->customer);
        $document        = $this->buildDocument(
            businessId:   $invoice->company_id,
            customerId:   $invoice->customer_id,
            type:         'invoice',
            number:       $invoice->invoice_number,
            issueDate:    $invoice->invoice_date,
            dueDate:      $invoice->due_date,
            items:        $invoice->items,
            notes:        $invoice->notes,
            terms:        $invoice->terms,
            templateId:   $invoice->company->getBizDocsTemplateId('invoice')
        );

        $storedTotals = [
            'subtotal'  => (float) $invoice->subtotal,
            'tax'       => (float) $invoice->tax_amount,
            'discount'  => (float) $invoice->discount_amount,
            'grand'     => (float) $invoice->total_amount,
        ];

        return $this->generatePdfWithBizDocs($document, $businessProfile, $customer, $storedTotals);
    }

    /**
     * Generate PDF for a quotation using BizDocs PdfGenerationService directly.
     */
    public function generateQuotationPdf(QuotationModel $quotation): string
    {
        $quotation->loadMissing(['company', 'customer', 'items']);

        $businessProfile = $this->buildBusinessProfile($quotation->company);
        $customer        = $this->buildCustomer($quotation->customer);
        $document        = $this->buildDocument(
            businessId:   $quotation->company_id,
            customerId:   $quotation->customer_id,
            type:         'quotation',
            number:       $quotation->quotation_number,
            issueDate:    $quotation->quotation_date,
            dueDate:      $quotation->expiry_date,
            items:        $quotation->items,
            notes:        $quotation->notes,
            terms:        $quotation->terms,
            templateId:   $quotation->company->getBizDocsTemplateId('quotation')
        );

        $storedTotals = [
            'subtotal'  => (float) $quotation->subtotal,
            'tax'       => (float) $quotation->tax_amount,
            'discount'  => (float) $quotation->discount_amount,
            'grand'     => (float) $quotation->total_amount,
        ];

        return $this->generatePdfWithBizDocs($document, $businessProfile, $customer, $storedTotals);
    }

    /**
     * Generate PDF receipt for a payment using BizDocs PdfGenerationService directly.
     */
    public function generateReceiptPdf(PaymentModel $payment): string
    {
        $payment->loadMissing(['company', 'customer', 'allocations.invoice']);

        // Build items from allocations
        $items = collect();
        if ($payment->allocations && $payment->allocations->count() > 0) {
            foreach ($payment->allocations as $allocation) {
                $items->push((object)[
                    'description'      => 'Payment for Invoice ' . ($allocation->invoice->invoice_number ?? '#' . $allocation->invoice_id),
                    'quantity'         => 1,
                    'unit_price'       => $allocation->amount,
                    'line_total'       => $allocation->amount,
                    'tax_rate'         => 0,
                    'dimensions'       => null,
                    'dimensions_value' => 1,
                ]);
            }
        } else {
            $items->push((object)[
                'description'      => $payment->notes ?? 'Payment received',
                'quantity'         => 1,
                'unit_price'       => $payment->amount,
                'line_total'       => $payment->amount,
                'tax_rate'         => 0,
                'dimensions'       => null,
                'dimensions_value' => 1,
            ]);
        }

        $businessProfile = $this->buildBusinessProfile($payment->company);
        $customer        = $this->buildCustomer($payment->customer);
        $document        = $this->buildDocument(
            businessId:   $payment->company_id,
            customerId:   $payment->customer_id,
            type:         'receipt',
            number:       $payment->receipt_number ?? ('RCPT-' . $payment->id),
            issueDate:    $payment->payment_date,
            dueDate:      null,
            items:        $items,
            notes:        $payment->notes,
            terms:        null,
            templateId:   $payment->company->getBizDocsTemplateId('receipt')
        );

        return $this->generatePdfWithBizDocs($document, $businessProfile, $customer);
    }

    /**
     * Generate print stationery (delegates to BizDocs stationery service).
     */
    public function generateStationery(array $config): string
    {
        $stationeryService = app(\App\Application\BizDocs\Services\StationeryGeneratorService::class);
        $company = CompanyModel::findOrFail($config['company_id']);
        $businessProfile = $this->buildBusinessProfile($company);

        return $stationeryService->generate(
            businessProfile:    $businessProfile,
            templateModel:      null,
            documentType:       $config['document_type'],
            quantity:           $config['quantity'],
            documentsPerPage:   $config['documents_per_page'],
            startingNumber:     $config['starting_number'],
            pageSize:           $config['page_size'] ?? 'A4',
            rowCount:           $config['row_count'] ?? null
        );
    }

    /**
     * Build a BizDocs BusinessProfile from a CMS Company.
     */
    private function buildBusinessProfile(CompanyModel $company): BusinessProfile
    {
        return BusinessProfile::fromPersistence(
            id:                            $company->id,
            userId:                        0, // Not applicable for CMS
            businessName:                  $company->name,
            address:                       $company->address ?? 'N/A',
            phone:                         $company->phone ?? 'N/A',
            email:                         $company->email,
            logo:                          $company->logo_path,
            tpin:                          $company->tax_number,
            website:                       $company->website,
            bankName:                      $company->settings['bank_name'] ?? null,
            bankAccount:                   $company->settings['bank_account'] ?? null,
            bankBranch:                    $company->settings['bank_branch'] ?? null,
            defaultCurrency:               $company->settings['currency'] ?? 'ZMW',
            defaultTaxRate:                16.0,
            defaultTerms:                  null,
            defaultNotes:                  null,
            defaultPaymentInstructions:    $company->invoice_footer,
            signatureImage:                $company->settings['signature_image'] ?? null,
            stampImage:                    null,
            preparedBy:                    null
        );
    }

    /**
     * Build a BizDocs Customer from a CMS Customer.
     */
    private function buildCustomer($cmsCustomer): Customer
    {
        return Customer::fromPersistence(
            id:         $cmsCustomer->id ?? 0,
            businessId: 0,
            name:       $cmsCustomer->name ?? 'Customer',
            address:    $cmsCustomer->address,
            phone:      $cmsCustomer->phone,
            email:      $cmsCustomer->email,
            tpin:       $cmsCustomer->tax_number ?? null,
            notes:      null
        );
    }

    /**
     * Build a BizDocs Document from CMS invoice/quotation/payment items.
     */
    private function buildDocument(
        int $businessId,
        int $customerId,
        string $type,
        string $number,
        string $issueDate,
        ?string $dueDate,
        $items,
        ?string $notes,
        ?string $terms,
        ?int $templateId
    ): Document {
        $document = Document::create(
            businessId:   $businessId,
            customerId:   $customerId,
            type:         DocumentType::fromString($type),
            number:       DocumentNumber::fromString($number),
            issueDate:    new \DateTimeImmutable($issueDate),
            currency:     'ZMW',
            templateId:   $templateId,
            dueDate:      $dueDate ? new \DateTimeImmutable($dueDate) : null,
            notes:        $notes,
            terms:        $terms,
        );

        foreach ($items as $index => $item) {
            $unitPrice = is_object($item) ? $item->unit_price : $item['unit_price'];
            $taxRate   = is_object($item) ? ($item->tax_rate ?? 0) : ($item['tax_rate'] ?? 0);
            $dims      = is_object($item) ? ($item->dimensions ?? null) : ($item['dimensions'] ?? null);
            $dimsVal   = is_object($item) ? (float)($item->dimensions_value ?? 1) : (float)($item['dimensions_value'] ?? 1);
            $qty       = is_object($item) ? (float)$item->quantity : (float)$item['quantity'];
            $desc      = is_object($item) ? $item->description : $item['description'];

            // When dimensions are area-based (dimsVal != 1), pass quantity=1 to avoid
            // double-multiplying: DocumentItem does dimensionsValue × quantity × unitPrice.
            $hasAreaDimensions = $dims !== null && $dims !== '' && $dimsVal != 1;
            $effectiveDimsVal  = $hasAreaDimensions ? $dimsVal : 1;
            $effectiveQty      = $hasAreaDimensions ? 1 : $qty;

            $document->addItem(DocumentItem::create(
                description:     $desc,
                quantity:        $effectiveQty,
                unitPrice:       Money::fromAmount((int)((float)$unitPrice * 100), 'ZMW'),
                taxRate:         (float)$taxRate,
                discountAmount:  Money::fromAmount(0, 'ZMW'),
                sortOrder:       $index,
                dimensions:      $dims,
                dimensionsValue: $effectiveDimsVal
            ));
        }

        return $document;
    }

    /**
     * Generate PDF using BizDocs PdfGenerationService with mocked repositories
     * so it uses our CMS entities instead of looking up BizDocs database records.
     */
    private function generatePdfWithBizDocs(Document $document, BusinessProfile $businessProfile, Customer $customer, array $storedTotals = []): string
    {
        $mockBusinessRepo = new class($businessProfile) implements \App\Domain\BizDocs\BusinessIdentity\Repositories\BusinessProfileRepositoryInterface {
            public function __construct(private BusinessProfile $profile) {}
            public function findById(int $id): ?BusinessProfile { return $this->profile; }
            public function findByUserId(int $userId): ?BusinessProfile { return $this->profile; }
            public function save(BusinessProfile $profile): BusinessProfile { return $profile; }
            public function delete(int $id): bool { return true; }
        };

        $mockCustomerRepo = new class($customer) implements \App\Domain\BizDocs\CustomerManagement\Repositories\CustomerRepositoryInterface {
            public function __construct(private Customer $customer) {}
            public function findById(int $id): ?Customer { return $this->customer; }
            public function findByBusiness(int $businessId, int $page = 1, int $perPage = 20): array { return [$this->customer]; }
            public function findByPhone(int $businessId, string $phone): ?Customer { return null; }
            public function findByEmail(int $businessId, string $email): ?Customer { return null; }
            public function search(int $businessId, string $query, int $page = 1, int $perPage = 20): array { return []; }
            public function save(Customer $customer): Customer { return $customer; }
            public function delete(int $id): bool { return true; }
            public function countByBusiness(int $businessId): int { return 1; }
        };

        $pdfService = new PdfGenerationService($mockBusinessRepo, $mockCustomerRepo, $this->fileStorageService);

        return $pdfService->generatePdfContent($document, $storedTotals);
    }
}
