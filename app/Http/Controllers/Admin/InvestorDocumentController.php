<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Investor\Services\DocumentManagementService;
use App\Domain\Investor\Repositories\InvestmentRoundRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InvestorDocumentController extends Controller
{
    public function __construct(
        private readonly DocumentManagementService $documentService,
        private readonly InvestmentRoundRepositoryInterface $roundRepository
    ) {}

    /**
     * Display a listing of investor documents
     */
    public function index()
    {
        $documentsGrouped = $this->documentService->getAllDocumentsGrouped();
        $stats = $this->documentService->getDocumentStats();
        $categories = $this->documentService->getAvailableCategories();

        return Inertia::render('Admin/Investor/Documents/Index', [
            'documentsGrouped' => $documentsGrouped,
            'stats' => $stats,
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new document
     */
    public function create()
    {
        $categories = $this->documentService->getAvailableCategories();
        $investmentRounds = $this->roundRepository->findAll();

        return Inertia::render('Admin/Investor/Documents/Create', [
            'categories' => $categories,
            'investmentRounds' => array_map(fn($round) => [
                'id' => $round->getId(),
                'name' => $round->getName(),
                'status' => $round->getStatus()->value(),
            ], $investmentRounds),
        ]);
    }

    /**
     * Store a newly created document
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|in:investment_agreements,financial_reports,tax_documents,company_updates,governance,certificates',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,txt|max:10240', // 10MB
            'visible_to_all' => 'boolean',
            'investment_round_id' => 'nullable|exists:investment_rounds,id',
        ]);

        try {
            $document = $this->documentService->uploadDocument(
                file: $request->file('file'),
                title: $validated['title'],
                description: $validated['description'] ?? '',
                category: $validated['category'],
                uploadedBy: Auth::id(),
                visibleToAll: $validated['visible_to_all'] ?? true,
                investmentRoundId: $validated['investment_round_id'] ?? null
            );

            return redirect()
                ->route('admin.investor-documents.index')
                ->with('success', 'Document uploaded successfully.');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['file' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show the form for editing a document
     */
    public function edit(int $id)
    {
        $document = $this->documentService->getDocumentById($id);
        
        if (!$document) {
            return redirect()
                ->route('admin.investor-documents.index')
                ->with('error', 'Document not found.');
        }

        $categories = $this->documentService->getAvailableCategories();
        $investmentRounds = $this->roundRepository->findAll();

        return Inertia::render('Admin/Investor/Documents/Edit', [
            'document' => $document->toArray(),
            'categories' => $categories,
            'investmentRounds' => array_map(fn($round) => [
                'id' => $round->getId(),
                'name' => $round->getName(),
                'status' => $round->getStatus()->value(),
            ], $investmentRounds),
        ]);
    }

    /**
     * Update the specified document
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'visible_to_all' => 'boolean',
            'investment_round_id' => 'nullable|exists:investment_rounds,id',
        ]);

        try {
            $this->documentService->updateDocument(
                documentId: $id,
                title: $validated['title'],
                description: $validated['description'] ?? '',
                visibleToAll: $validated['visible_to_all'] ?? true,
                investmentRoundId: $validated['investment_round_id'] ?? null
            );

            return redirect()
                ->route('admin.investor-documents.index')
                ->with('success', 'Document updated successfully.');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified document
     */
    public function destroy(int $id)
    {
        try {
            $this->documentService->deleteDocument($id);

            return redirect()
                ->route('admin.investor-documents.index')
                ->with('success', 'Document deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Archive the specified document
     */
    public function archive(int $id)
    {
        try {
            $this->documentService->archiveDocument($id);

            return redirect()
                ->route('admin.investor-documents.index')
                ->with('success', 'Document archived successfully.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download a document
     */
    public function download(int $id)
    {
        try {
            $fileData = $this->documentService->downloadDocument(
                documentId: $id,
                investorAccountId: 0, // Admin download
                ipAddress: request()->ip(),
                userAgent: request()->userAgent()
            );

            return response()->download(
                $fileData['path'],
                $fileData['name'],
                ['Content-Type' => $fileData['mime_type']]
            );

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get document access logs
     */
    public function accessLogs(int $id)
    {
        $document = $this->documentService->getDocumentById($id);
        
        if (!$document) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        $accessLogs = $this->documentService->getDocumentAccessLogs($id);

        return response()->json([
            'document' => $document->toArray(),
            'access_logs' => $accessLogs,
        ]);
    }
}