<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\DocumentModel;
use App\Infrastructure\Persistence\Eloquent\CMS\DocumentVersionModel;
use App\Infrastructure\Persistence\Eloquent\CMS\DocumentShareModel;
use App\Infrastructure\Persistence\Eloquent\CMS\DocumentSignatureModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = DocumentModel::where('company_id', $request->user()->current_company_id)
            ->with(['category', 'uploadedBy'])
            ->when($request->category_id, fn($q, $cat) => $q->where('category_id', $cat))
            ->latest()
            ->paginate(20);

        return Inertia::render('CMS/Documents/Index', ['documents' => $documents]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:cms_document_categories,id',
            'title' => 'required|string|max:255',
            'document_number' => 'nullable|string|max:100',
            'file_path' => 'required|string',
            'file_name' => 'required|string|max:255',
            'file_size' => 'required|integer',
            'mime_type' => 'required|string|max:100',
            'description' => 'nullable|string',
            'tags' => 'nullable|string',
            'expiry_date' => 'nullable|date',
        ]);

        $document = DocumentModel::create([
            ...$validated,
            'company_id' => $request->user()->current_company_id,
            'uploaded_by' => $request->user()->id,
            'version' => 1,
        ]);

        return back()->with('success', 'Document uploaded successfully');
    }

    public function show(int $id)
    {
        $document = DocumentModel::with(['versions', 'shares', 'signatures', 'accessLogs'])
            ->findOrFail($id);

        // Log access
        $document->accessLogs()->create([
            'user_id' => auth()->id(),
            'action' => 'view',
            'ip_address' => request()->ip(),
        ]);

        return Inertia::render('CMS/Documents/Show', ['document' => $document]);
    }

    public function update(Request $request, int $id)
    {
        $document = DocumentModel::findOrFail($id);
        $document->update($request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'tags' => 'nullable|string',
            'expiry_date' => 'nullable|date',
        ]));

        return back()->with('success', 'Document updated');
    }

    public function destroy(int $id)
    {
        $document = DocumentModel::findOrFail($id);
        $document->delete();

        return redirect()->route('cms.documents.index')
            ->with('success', 'Document deleted');
    }

    public function uploadVersion(Request $request, int $id)
    {
        $validated = $request->validate([
            'file_path' => 'required|string',
            'file_name' => 'required|string|max:255',
            'file_size' => 'required|integer',
            'version_notes' => 'nullable|string',
        ]);

        $document = DocumentModel::findOrFail($id);
        
        $document->versions()->create([
            ...$validated,
            'version_number' => $document->version + 1,
            'uploaded_by' => $request->user()->id,
        ]);

        $document->increment('version');

        return back()->with('success', 'New version uploaded');
    }

    public function share(Request $request, int $id)
    {
        $validated = $request->validate([
            'shared_with_type' => 'required|in:user,customer,supplier,public',
            'shared_with_id' => 'nullable|integer',
            'permission' => 'required|in:view,download,edit',
            'expiry_date' => 'nullable|date',
        ]);

        $document = DocumentModel::findOrFail($id);
        
        $document->shares()->create([
            ...$validated,
            'shared_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Document shared');
    }

    public function sign(Request $request, int $id)
    {
        $validated = $request->validate([
            'signature_data' => 'required|string',
            'signature_type' => 'required|in:digital,electronic,scanned',
        ]);

        $document = DocumentModel::findOrFail($id);
        
        $document->signatures()->create([
            ...$validated,
            'signer_id' => $request->user()->id,
            'signer_name' => $request->user()->name,
            'signer_email' => $request->user()->email,
            'signed_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Document signed');
    }
}
