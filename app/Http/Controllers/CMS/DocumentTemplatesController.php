<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentTemplateModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentTemplatesController extends Controller
{
    /**
     * Show the template gallery for a given document type.
     * Reuses BizDocs templates — no duplication.
     */
    public function index(Request $request)
    {
        $company = $request->user()->cmsUser->company;
        $documentType = $request->query('type', 'invoice');

        $templates = DocumentTemplateModel::where('visibility', 'industry')
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get()
            ->map(fn($t) => [
                'id'                 => $t->id,
                'name'               => $t->name,
                'document_type'      => $t->document_type,
                'industry_category'  => $t->industry_category,
                'layout_file'        => $t->layout_file,
                'thumbnail_path'     => $t->thumbnail_path,
                'is_default'         => $t->is_default,
                'template_structure' => $t->template_structure,
            ]);

        // Current preferences per document type
        $preferences = $company->settings['bizdocs_template_preferences'] ?? [];

        return Inertia::render('CMS/Settings/DocumentTemplates', [
            'templates'      => $templates,
            'documentType'   => $documentType,
            'preferences'    => $preferences,
            'company'        => $company->only(['id', 'name', 'has_bizdocs_module']),
        ]);
    }

    /**
     * Save a template preference for a document type.
     */
    public function setTemplate(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|in:invoice,quotation,receipt',
            'template_id'   => 'required|exists:bizdocs_document_templates,id',
        ]);

        $company = $request->user()->cmsUser->company;
        $company->setBizDocsTemplateId($validated['document_type'], $validated['template_id']);

        return back()->with('success', 'Template saved successfully');
    }

    /**
     * Render a live HTML preview of a template with sample CMS data.
     * Returns raw HTML (used in an iframe).
     * Uses a signed URL so the iframe can load without session cookies.
     */
    public function preview(Request $request, int $id)
    {
        // Validate the signature
        if (!$request->hasValidSignature()) {
            abort(403);
        }

        $template = DocumentTemplateModel::findOrFail($id);

        $viewFile = 'bizdocs.pdf.templates.' . $template->layout_file;
        $isSpecificTemplate = view()->exists($viewFile);

        if (!$isSpecificTemplate) {
            $viewFile = 'bizdocs.pdf.document';
        }

        $sampleData = $this->buildSampleDataAnonymous($template, null, $isSpecificTemplate);

        return response(view($viewFile, $sampleData)->render())
            ->header('Content-Type', 'text/html')
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    /**
     * Generate a signed preview URL for a template (called from authenticated context).
     */
    public function previewUrl(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $url = \URL::signedRoute('cms.settings.document-templates.preview', ['id' => $id], now()->addHour());
        return response()->json(['url' => $url]);
    }

    private function buildSampleData($company, $template): array
    {
        $viewFile = 'bizdocs.pdf.templates.' . $template->layout_file;
        $isSpecificTemplate = view()->exists($viewFile);
        return $this->buildSampleDataAnonymous($template, $company, $isSpecificTemplate);
    }

    private function buildSampleDataAnonymous($template, $company = null, bool $isSpecificTemplate = true): array
    {
        $document = new \App\Domain\CMS\BizDocs\Adapters\CmsDocumentWrapper([
            'type'                => 'invoice',
            'number'              => 'INV-2026-001',
            'issueDate'           => new \DateTimeImmutable(),
            'dueDate'             => new \DateTimeImmutable('+30 days'),
            'notes'               => 'Thank you for your business.',
            'terms'               => 'Payment due within 30 days.',
            'paymentInstructions' => $company?->invoice_footer ?? 'Bank: First National Bank | Account: 1234567890',
            'currency'            => $company?->settings['currency'] ?? 'ZMW',
            'items'               => [
                ['description' => 'Professional Services', 'quantity' => 5,  'unit_price' => 200.00, 'line_total' => 1000.00, 'tax_rate' => 16],
                ['description' => 'Consultation Fee',      'quantity' => 2,  'unit_price' => 350.00, 'line_total' => 700.00,  'tax_rate' => 16],
                ['description' => 'Software License',      'quantity' => 1,  'unit_price' => 500.00, 'line_total' => 500.00,  'tax_rate' => 0],
            ],
            'subtotal'      => 2200.00,
            'taxTotal'      => 272.00,
            'discountTotal' => 0.00,
            'grandTotal'    => 2472.00,
            'extra'         => [],
        ]);

        // Use a mock company object when no real company is available
        $mockCompany = $company ?? (object)[
            'name'          => 'Your Company Name',
            'address'       => '123 Business Street, Lusaka, Zambia',
            'phone'         => '+260 97 1234567',
            'email'         => 'info@yourcompany.com',
            'tax_number'    => 'TPIN123456789',
            'logo_path'     => null,
            'invoice_footer'=> 'Bank: First National Bank | Account: 1234567890',
            'website'       => null,
            'settings'      => [],
            'id'            => 0,
        ];

        $businessProfile = new \App\Domain\CMS\BizDocs\Adapters\CmsBusinessProfileWrapper($mockCompany);

        $customer = new \App\Domain\CMS\BizDocs\Adapters\CmsCustomerWrapper((object)[
            'name'       => 'Sample Customer Ltd',
            'email'      => 'customer@example.com',
            'phone'      => '+260 97 9876543',
            'address'    => '456 Client Avenue, Ndola, Zambia',
            'tax_number' => null,
            'id'         => 0,
        ]);

        // Specific templates (bizdocs.pdf.templates.*) use raw int cents:
        //   $totals['subtotal'] / 100, $totals['taxTotal'] / 100, etc.
        // The generic template (bizdocs.pdf.document) uses objects:
        //   $totals['subtotal']->amount(), $totals['tax_total']->amount()
        if ($isSpecificTemplate) {
            $totals = [
                'subtotal'      => 220000,
                'taxTotal'      => 27200,
                'discountTotal' => 0,
                'grandTotal'    => 247200,
                // Also provide snake_case aliases for any template that mixes both
                'tax_total'     => 27200,
                'discount_total'=> 0,
                'grand_total'   => 247200,
            ];
        } else {
            $totals = [
                'subtotal'       => new \App\Domain\CMS\BizDocs\Adapters\CmsMoneyWrapper(2200.00),
                'tax_total'      => new \App\Domain\CMS\BizDocs\Adapters\CmsMoneyWrapper(272.00),
                'discount_total' => new \App\Domain\CMS\BizDocs\Adapters\CmsMoneyWrapper(0.00),
                'grand_total'    => new \App\Domain\CMS\BizDocs\Adapters\CmsMoneyWrapper(2472.00),
            ];
        }

        $sampleItems = [
            (object)['description' => 'Professional Services', 'quantity' => 5, 'unitPrice' => 20000, 'taxRate' => 16, 'discountAmount' => 0, 'dimensions' => null, 'dimensionsValue' => 1, 'lineTotal' => 100000],
            (object)['description' => 'Consultation Fee',      'quantity' => 2, 'unitPrice' => 35000, 'taxRate' => 16, 'discountAmount' => 0, 'dimensions' => null, 'dimensionsValue' => 1, 'lineTotal' => 70000],
            (object)['description' => 'Software License',      'quantity' => 1, 'unitPrice' => 50000, 'taxRate' => 0,  'discountAmount' => 0, 'dimensions' => null, 'dimensionsValue' => 1, 'lineTotal' => 50000],
        ];

        return [
            'document'        => $document,
            'businessProfile' => $businessProfile,
            'customer'        => $customer,
            'totals'          => $totals,
            'logoPath'        => null,
            'signaturePath'   => null,
            'isPdf'           => false,
            'template'        => $template,
            'items'           => $sampleItems,
        ];
    }
}
