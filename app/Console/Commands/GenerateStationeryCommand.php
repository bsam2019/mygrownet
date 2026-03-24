<?php

namespace App\Console\Commands;

use App\Application\BizDocs\DTOs\GenerateStationeryDTO;
use App\Application\BizDocs\UseCases\GenerateStationeryUseCase;
use Illuminate\Console\Command;

class GenerateStationeryCommand extends Command
{
    protected $signature = 'bizdocs:generate-stationery 
                            {business_id : The business profile user ID}
                            {document_type : Type of document (invoice, receipt, quotation, etc.)}
                            {template_id : Template ID to use}
                            {quantity : Number of documents to generate}
                            {--per-page=1 : Documents per page (1, 2, or 4)}
                            {--start=1 : Starting document number}';

    protected $description = 'Generate print-ready stationery with blank, pre-numbered documents';

    public function handle(GenerateStationeryUseCase $useCase): int
    {
        $businessId = (int) $this->argument('business_id');
        $documentType = $this->argument('document_type');
        $templateId = (int) $this->argument('template_id');
        $quantity = (int) $this->argument('quantity');
        $documentsPerPage = (int) $this->option('per-page');
        $startNumber = (int) $this->option('start');

        // Validate inputs
        if (!in_array($documentsPerPage, [1, 2, 4])) {
            $this->error('Documents per page must be 1, 2, or 4');
            return self::FAILURE;
        }

        if ($quantity < 1 || $quantity > 500) {
            $this->error('Quantity must be between 1 and 500');
            return self::FAILURE;
        }

        $this->info("Generating {$quantity} {$documentType} documents...");
        $this->info("Layout: {$documentsPerPage} document(s) per page");

        try {
            // Format starting number
            $prefix = $this->getDocumentPrefix($documentType);
            $startingNumber = sprintf('%s-%s-%04d', $prefix, date('Y'), $startNumber);

            $dto = new GenerateStationeryDTO(
                businessId: $businessId,
                documentType: $documentType,
                templateId: $templateId,
                quantity: $quantity,
                documentsPerPage: $documentsPerPage,
                startingNumber: $startingNumber,
            );

            $pdfPath = $useCase->execute($dto);

            $this->info("✓ Stationery generated successfully!");
            $this->info("PDF saved to: storage/app/public/{$pdfPath}");
            $this->info("Download URL: " . url("storage/{$pdfPath}"));

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to generate stationery: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function getDocumentPrefix(string $type): string
    {
        return match($type) {
            'invoice' => 'INV',
            'receipt' => 'RCPT',
            'quotation' => 'QTN',
            'delivery_note' => 'DN',
            'proforma_invoice' => 'PI',
            'credit_note' => 'CN',
            'debit_note' => 'DBN',
            'purchase_order' => 'PO',
            default => 'DOC',
        };
    }
}
