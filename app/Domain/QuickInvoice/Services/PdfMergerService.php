<?php

namespace App\Domain\QuickInvoice\Services;

use setasign\Fpdi\Fpdi;
use Illuminate\Http\UploadedFile;

/**
 * Service for merging PDFs with attachments
 */
class PdfMergerService
{
    /**
     * Merge main PDF with attachments
     *
     * @param string $mainPdfContent The main document PDF content
     * @param array $attachments Array of UploadedFile objects
     * @return string Merged PDF content
     */
    public function mergeWithAttachments(string $mainPdfContent, array $attachments): string
    {
        if (empty($attachments)) {
            return $mainPdfContent;
        }

        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);

        // Add main document pages
        $tempMain = $this->createTempFile($mainPdfContent);
        
        try {
            $pageCount = $pdf->setSourceFile($tempMain);
            
            for ($i = 1; $i <= $pageCount; $i++) {
                // Import page
                $tplId = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tplId);
                
                // Add page with same orientation and size
                $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                
                // Use the imported page
                $pdf->useTemplate($tplId);
            }
            
            // Add attachments
            foreach ($attachments as $attachment) {
                $this->addAttachment($pdf, $attachment);
            }
            
            return $pdf->Output('S'); // Return as string
            
        } finally {
            // Clean up temp file
            if (file_exists($tempMain)) {
                unlink($tempMain);
            }
        }
    }

    /**
     * Add an attachment to the PDF
     *
     * @param Fpdi $pdf
     * @param UploadedFile $attachment
     * @return void
     */
    private function addAttachment(Fpdi $pdf, UploadedFile $attachment): void
    {
        $extension = strtolower($attachment->getClientOriginalExtension());
        
        if ($extension === 'pdf') {
            $this->addPdfAttachment($pdf, $attachment);
        } else {
            $this->addImageAttachment($pdf, $attachment);
        }
    }

    /**
     * Add a PDF attachment
     *
     * @param Fpdi $pdf
     * @param UploadedFile $attachment
     * @return void
     */
    private function addPdfAttachment(Fpdi $pdf, UploadedFile $attachment): void
    {
        $tempFile = $attachment->getRealPath();
        
        try {
            $pageCount = $pdf->setSourceFile($tempFile);
            
            for ($i = 1; $i <= $pageCount; $i++) {
                $tplId = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tplId);
                
                $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($tplId);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to add PDF attachment', [
                'file' => $attachment->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            
            // Add error page instead
            $this->addErrorPage($pdf, $attachment->getClientOriginalName(), 'Could not process PDF');
        }
    }

    /**
     * Add an image attachment
     *
     * @param Fpdi $pdf
     * @param UploadedFile $attachment
     * @return void
     */
    private function addImageAttachment(Fpdi $pdf, UploadedFile $attachment): void
    {
        try {
            $tempPath = $attachment->getRealPath();
            
            // Get image dimensions
            $imageInfo = @getimagesize($tempPath);
            
            if (!$imageInfo) {
                throw new \Exception('Invalid image file or cannot read image');
            }
            
            [$width, $height] = $imageInfo;
            
            // Calculate page size (A4 = 210mm x 297mm)
            $maxWidth = 190; // mm (with margins)
            $maxHeight = 277; // mm (with margins)
            
            // Calculate scaling to fit page
            $scale = min($maxWidth / ($width * 0.264583), $maxHeight / ($height * 0.264583));
            $scaledWidth = $width * 0.264583 * $scale; // Convert px to mm
            $scaledHeight = $height * 0.264583 * $scale;
            
            // Add page
            $pdf->AddPage();
            
            // Center image on page
            $x = (210 - $scaledWidth) / 2;
            $y = (297 - $scaledHeight) / 2;
            
            // Determine image type and add to PDF
            $extension = strtolower($attachment->getClientOriginalExtension());
            
            // Add image based on type
            if ($extension === 'png') {
                $pdf->Image($tempPath, $x, $y, $scaledWidth, $scaledHeight, 'PNG');
            } elseif (in_array($extension, ['jpg', 'jpeg'])) {
                $pdf->Image($tempPath, $x, $y, $scaledWidth, $scaledHeight, 'JPG');
            } else {
                throw new \Exception('Unsupported image format: ' . $extension);
            }
            
            // Add filename at bottom
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(128, 128, 128);
            $pdf->SetXY(10, 287);
            $pdf->Cell(0, 5, 'Attachment: ' . $attachment->getClientOriginalName(), 0, 0, 'C');
            
        } catch (\Exception $e) {
            \Log::warning('Failed to add image attachment', [
                'file' => $attachment->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            
            // Add error page instead
            $this->addErrorPage($pdf, $attachment->getClientOriginalName(), 'Could not process image');
        }
    }

    /**
     * Add an error page when attachment processing fails
     *
     * @param Fpdi $pdf
     * @param string $filename
     * @param string $error
     * @return void
     */
    private function addErrorPage(Fpdi $pdf, string $filename, string $error): void
    {
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor(200, 0, 0);
        $pdf->Cell(0, 50, 'Attachment Error', 0, 1, 'C');
        
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 10, "File: {$filename}\n\nError: {$error}", 0, 'C');
    }

    /**
     * Create a temporary file from content
     *
     * @param string $content
     * @return string Path to temp file
     */
    private function createTempFile(string $content): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf_');
        file_put_contents($tempFile, $content);
        return $tempFile;
    }
}
