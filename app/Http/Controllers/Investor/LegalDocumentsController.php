<?php

namespace App\Http\Controllers\Investor;

use App\Application\DTOs\ShareCertificateDTO;
use App\Application\UseCases\Investor\GenerateShareCertificateUseCase;
use App\Application\UseCases\Investor\GetInvestorCertificatesUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LegalDocumentsController extends Controller
{
    public function __construct(
        private readonly GetInvestorCertificatesUseCase $getCertificatesUseCase,
        private readonly GenerateShareCertificateUseCase $generateCertificateUseCase
    ) {}

    /**
     * Display legal documents dashboard
     */
    public function index(Request $request)
    {
        $investorId = $request->user()->id;

        // Get certificates through use case
        $certificates = $this->getCertificatesUseCase->execute($investorId);

        // Convert to DTOs
        $certificateDTOs = array_map(
            fn($cert) => ShareCertificateDTO::fromEntity($cert)->toArray(),
            $certificates
        );

        return Inertia::render('Investor/LegalDocuments', [
            'certificates' => $certificateDTOs,
        ]);
    }

    /**
     * Generate certificate for investment
     */
    public function generate(Request $request, int $investmentId)
    {
        try {
            $certificate = $this->generateCertificateUseCase->execute($investmentId);
            $dto = ShareCertificateDTO::fromEntity($certificate);

            return response()->json([
                'success' => true,
                'certificate' => $dto->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download certificate PDF
     */
    public function download(Request $request, int $certificateId)
    {
        $certificates = $this->getCertificatesUseCase->execute($request->user()->id);
        
        $certificate = collect($certificates)->firstWhere('id', $certificateId);

        if (!$certificate || !$certificate->getPdfPath()) {
            abort(404, 'Certificate not found');
        }

        $pdfPath = $certificate->getPdfPath();

        if (!Storage::disk('public')->exists($pdfPath)) {
            abort(404, 'Certificate PDF not found');
        }

        return Storage::disk('public')->download(
            $pdfPath,
            "Share-Certificate-{$certificate->getCertificateNumber()->value()}.pdf"
        );
    }
}
