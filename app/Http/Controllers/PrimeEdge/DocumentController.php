<?php

namespace App\Http\Controllers\PrimeEdge;

use App\Http\Controllers\Controller;
use App\Domain\PrimeEdge\Repositories\DocumentRepositoryInterface;
use App\Domain\PrimeEdge\Repositories\EngagementRepositoryInterface;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\DocumentType;
use App\Domain\PrimeEdge\Entities\Document;
use App\Domain\PrimeEdge\Entities\Engagement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function __construct(
        private DocumentRepositoryInterface $documentRepository,
        private ?EngagementRepositoryInterface $engagementRepository = null,
    ) {}

    public function create()
    {
        $clientId = auth()->guard('primeedge')->id();
        $engagements = $this->engagementRepository
            ? array_map(
                fn(Engagement $e) => [
                    'id' => $e->id()->toString(),
                    'service_name' => $e->type()->label(),
                ],
                $this->engagementRepository->findActiveByClientId(ClientId::fromString($clientId))
            )
            : [];

        return Inertia::render('PrimeEdge/Documents/Create', [
            'engagements' => $engagements,
        ]);
    }

    public function index()
    {
        $clientId = auth()->guard('primeedge')->id();
        $documents = array_map(
            fn(Document $doc) => [
                'id' => $doc->id()->toString(),
                'name' => $doc->name(),
                'type' => $doc->type()->label(),
                'fileSize' => $doc->fileSize(),
                'mimeType' => $doc->mimeType(),
                'createdAt' => $doc->createdAt()->format('Y-m-d H:i'),
            ],
            $this->documentRepository->findByClientId(ClientId::fromString($clientId))
        );

        return Inertia::render('PrimeEdge/Documents/Index', [
            'documents' => $documents,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:10240'],
            'engagement_id' => ['nullable', 'string'],
        ]);

        $file = $request->file('file');
        $path = $file->store('primeedge/documents', 'public');

        $document = Document::create(
            id: $this->documentRepository->nextId(),
            clientId: ClientId::fromString(auth()->guard('primeedge')->id()),
            name: $validated['name'],
            type: DocumentType::from($file->getClientOriginalExtension()),
            filePath: $path,
            mimeType: $file->getMimeType(),
            fileSize: $file->getSize(),
            engagementId: $validated['engagement_id'] ?? null,
        );

        $this->documentRepository->save($document);

        return redirect()->route('primeedge.documents.index')
            ->with('success', 'Document uploaded successfully.');
    }
}
