<?php

namespace App\Http\Controllers\BizDocs;

use App\Domain\BizDocs\BusinessIdentity\Repositories\BusinessProfileRepositoryInterface;
use App\Domain\BizDocs\DocumentManagement\Services\DocumentNumberingService;
use App\Http\Controllers\Controller;
use App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentSequenceModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function __construct(
        private readonly BusinessProfileRepositoryInterface $businessProfileRepository,
        private readonly DocumentNumberingService $numberingService
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        if (!$businessProfile) {
            return redirect()->route('bizdocs.setup');
        }

        return Inertia::render('BizDocs/Settings/Index', [
            'businessProfile' => $businessProfile->toArray(),
        ]);
    }

    public function numbering(Request $request)
    {
        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        if (!$businessProfile) {
            return redirect()->route('bizdocs.setup');
        }

        // Get current sequences for all document types
        $documentTypes = [
            'invoice',
            'receipt',
            'quotation',
            'delivery_note',
            'proforma_invoice',
            'credit_note',
            'debit_note',
            'purchase_order',
        ];

        $sequences = [];
        $year = now()->year;

        foreach ($documentTypes as $type) {
            $sequence = DocumentSequenceModel::where('business_id', $businessProfile->id())
                ->where('document_type', $type)
                ->where('year', $year)
                ->first();

            $sequences[$type] = [
                'prefix' => $sequence?->prefix ?? strtoupper(substr($type, 0, 3)),
                'padding' => $sequence?->padding ?? 4,
                'last_number' => $sequence?->last_number ?? 0,
                'next_number' => ($sequence?->last_number ?? 0) + 1,
                'include_year' => $sequence?->include_year ?? false,
            ];
        }

        return Inertia::render('BizDocs/Settings/Numbering', [
            'businessProfile' => $businessProfile->toArray(),
            'sequences' => $sequences,
            'currentYear' => $year,
        ]);
    }

    public function updateNumbering(Request $request)
    {
        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        if (!$businessProfile) {
            return response()->json(['error' => 'Business profile not found'], 404);
        }

        $validated = $request->validate([
            'document_type' => 'required|string|in:invoice,receipt,quotation,delivery_note,proforma_invoice,credit_note,debit_note,purchase_order',
            'prefix' => 'required|string|max:10',
            'padding' => 'required|integer|min:1|max:10',
            'include_year' => 'required|boolean',
            'reset_number' => 'nullable|integer|min:0',
        ]);

        $year = now()->year;

        $data = [
            'business_id' => $businessProfile->id(),
            'document_type' => $validated['document_type'],
            'year' => $year,
            'prefix' => $validated['prefix'],
            'padding' => $validated['padding'],
            'include_year' => $validated['include_year'],
        ];

        if (isset($validated['reset_number'])) {
            $data['last_number'] = $validated['reset_number'];
        }

        DocumentSequenceModel::updateOrCreate(
            [
                'business_id' => $businessProfile->id(),
                'document_type' => $validated['document_type'],
                'year' => $year,
            ],
            $data
        );

        return redirect()->back()->with('success', 'Document numbering settings updated successfully');
    }
}
