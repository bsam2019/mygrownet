<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing BizDocs Stationery PDF Generation...\n\n";

try {
    // Get business profile
    $businessRepo = app(\App\Domain\BizDocs\BusinessIdentity\Repositories\BusinessProfileRepositoryInterface::class);
    $businessProfile = $businessRepo->findByUserId(1); // Adjust user ID as needed
    
    if (!$businessProfile) {
        echo "ERROR: Business profile not found for user ID 1\n";
        echo "Please ensure you have a business profile set up.\n";
        exit(1);
    }
    
    echo "Business Profile: " . $businessProfile->businessName() . "\n";
    echo "Logo: " . ($businessProfile->logo() ? 'YES' : 'NO') . "\n";
    echo "TPIN: " . ($businessProfile->tpin() ?: 'Not set') . "\n\n";
    
    // Get a template
    $template = \App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentTemplateModel::first();
    if (!$template) {
        echo "ERROR: No templates found in the database\n";
        exit(1);
    }
    
    echo "Using template: " . $template->name . " (ID: " . $template->id . ")\n\n";
    
    // Test different documents per page configurations
    $testConfigs = [
        ['documents_per_page' => 1, 'quantity' => 2],
        ['documents_per_page' => 2, 'quantity' => 4],
        ['documents_per_page' => 4, 'quantity' => 8],
        ['documents_per_page' => 6, 'quantity' => 6],
    ];
    
    $stationeryService = app(\App\Application\BizDocs\Services\StationeryGeneratorService::class);
    
    foreach ($testConfigs as $config) {
        echo "Testing " . $config['documents_per_page'] . " receipts per page...\n";
        
        try {
            $dto = new \App\Application\BizDocs\DTOs\GenerateStationeryDTO(
                businessId: 1,
                documentType: 'receipt',
                templateId: $template->id,
                quantity: $config['quantity'],
                documentsPerPage: $config['documents_per_page'],
                startingNumber: 'RCPT-2026-0001',
                pageSize: 'A4',
                rowCount: null,
            );
            
            $pdfContent = $stationeryService->generate(
                businessProfile: $businessProfile,
                templateModel: $template,
                documentType: 'receipt',
                quantity: $config['quantity'],
                documentsPerPage: $config['documents_per_page'],
                startingNumber: 'RCPT-2026-0001',
                pageSize: 'A4',
                rowCount: null,
            );
            
            if (empty($pdfContent)) {
                echo "ERROR: Generated PDF is empty!\n";
                continue;
            }
            
            echo "SUCCESS: PDF generated (" . strlen($pdfContent) . " bytes)\n";
            
            // Save test PDF
            $filename = __DIR__ . "/test_stationery_{$config['documents_per_page']}_per_page.pdf";
            file_put_contents($filename, $pdfContent);
            echo "Saved to: " . $filename . "\n";
            
        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    echo "Stationery generation test completed!\n";
    echo "Check the generated PDF files to verify:\n";
    echo "- Correct number of receipts per page\n";
    echo "- Proper layout and positioning\n";
    echo "- Logo and business info display\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
