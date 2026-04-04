<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing BizDocs PDF Generation...\n\n";

try {
    // Get a sample document from the database
    $documentRepo = app(\App\Domain\BizDocs\DocumentManagement\Repositories\DocumentRepositoryInterface::class);
    
    // Try to find any document
    $documents = $documentRepo->findByBusinessId(1); // Adjust business ID as needed
    
    if (empty($documents)) {
        echo "No documents found. Creating a test document...\n";
        
        // You would need to create a test document here
        echo "Please create a document in the system first.\n";
        exit(1);
    }
    
    $document = $documents[0];
    echo "Testing with document: " . $document->number()->value() . " (Type: " . $document->type()->value() . ")\n";
    echo "Items count: " . count($document->items()) . "\n\n";
    
    // Test PDF generation
    $pdfService = app(\App\Application\BizDocs\Services\PdfGenerationService::class);
    
    echo "Generating PDF content...\n";
    $pdfContent = $pdfService->generatePdfContent($document);
    
    if (empty($pdfContent)) {
        echo "ERROR: Generated PDF is empty!\n";
        exit(1);
    }
    
    echo "SUCCESS: PDF generated successfully!\n";
    echo "PDF size: " . strlen($pdfContent) . " bytes\n";
    
    // Save test PDF
    $testFile = __DIR__ . '/test_document.pdf';
    file_put_contents($testFile, $pdfContent);
    echo "Test PDF saved to: " . $testFile . "\n";
    
    echo "\nChecking for common issues:\n";
    
    // Check if items are properly displayed
    $itemsCount = count($document->items());
    echo "- Items in document: " . $itemsCount . "\n";
    
    if ($itemsCount === 0) {
        echo "WARNING: Document has no items. This might be why receipts are missing.\n";
    }
    
    // Check business profile and customer
    $businessProfile = app(\App\Domain\BizDocs\BusinessIdentity\Repositories\BusinessProfileRepositoryInterface::class)
        ->findById($document->businessId());
    $customer = app(\App\Domain\BizDocs\CustomerManagement\Repositories\CustomerRepositoryInterface::class)
        ->findById($document->customerId());
    
    echo "- Business profile found: " . ($businessProfile ? 'YES' : 'NO') . "\n";
    echo "- Customer found: " . ($customer ? 'YES' : 'NO') . "\n";
    
    if (!$businessProfile || !$customer) {
        echo "ERROR: Missing business profile or customer data!\n";
    }
    
    echo "\nPDF generation test completed successfully!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
