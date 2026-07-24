<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\CustomerTagRepositoryInterface;
use Illuminate\Http\Request;

class CustomerTagController extends Controller
{
    public function __construct(
        private BusinessService $businessService,
        private CustomerTagRepositoryInterface $tagRepo,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $tags = $this->tagRepo->findByBusiness($business->id);

        return response()->json($tags);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:7',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $tag = $this->tagRepo->save(new \App\Domain\BizBoost\Entities\CustomerTag(
            id: null,
            businessId: $business->id,
            name: $validated['name'],
            color: $validated['color'] ?? '#3B82F6',
            createdAt: null,
            updatedAt: null,
        ));

        return response()->json($tag->toArray());
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:7',
        ]);

        $tag = $this->tagRepo->findById($id);
        if (!$tag) {
            abort(404);
        }

        $updatedTag = new \App\Domain\BizBoost\Entities\CustomerTag(
            id: $tag->id,
            businessId: $tag->businessId,
            name: $validated['name'],
            color: $validated['color'] ?? $tag->color,
            createdAt: $tag->createdAt,
            updatedAt: null,
        );
        $this->tagRepo->save($updatedTag);

        return response()->json($updatedTag->toArray());
    }

    public function destroy(Request $request, int $id)
    {
        $tag = $this->tagRepo->findById($id);
        if (!$tag) {
            abort(404);
        }
        $this->tagRepo->delete($id);

        return response()->json(['success' => true]);
    }
}