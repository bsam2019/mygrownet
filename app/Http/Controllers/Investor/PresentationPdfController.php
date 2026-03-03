<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PresentationPdfController extends Controller
{
    public function exportPdf(Request $request)
    {
        // Get presentation data from request or session
        $data = $request->all();

        // Generate PDF from presentation view
        $pdf = Pdf::loadView('pdf.presentation.slides', [
            'data' => $data,
            'title' => $data['title'] ?? 'MyGrowNet Presentation',
        ]);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'landscape');

        // Return PDF download
        return $pdf->download('mygrownet-presentation.pdf');
    }
}
