<?php

namespace App\Http\Controllers\LifePlus;

use App\Http\Controllers\Controller;
use App\Domain\LifePlus\Services\KnowledgeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KnowledgeController extends Controller
{
    public function __construct(protected KnowledgeService $knowledgeService) {}

    public function index(Request $request)
    {
        $filters = $request->only(['category', 'type', 'search']);

        return Inertia::render('LifePlus/Knowledge/Index', [
            'items' => $this->knowledgeService->getItems($filters),
            'categories' => $this->knowledgeService->getCategories(),
            'filters' => $filters,
        ]);
    }

    public function show(int $id)
    {
        $item = $this->knowledgeService->getItem($id);

        if (!$item) {
            return redirect()->route('lifeplus.knowledge.index')
                ->with('error', 'Item not found');
        }

        return Inertia::render('LifePlus/Knowledge/Show', [
            'item' => $item,
        ]);
    }

    public function dailyTip()
    {
        return response()->json($this->knowledgeService->getDailyTip());
    }

    public function download(int $id)
    {
        $success = $this->knowledgeService->downloadItem($id, auth()->id());

        if (!$success) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Downloaded for offline access']);
    }

    public function downloads()
    {
        return Inertia::render('LifePlus/Knowledge/Downloads', [
            'downloads' => $this->knowledgeService->getUserDownloads(auth()->id()),
        ]);
    }
}
