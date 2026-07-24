<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Api;

use App\Domain\GrowStream\Repositories\VideoCategoryRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private VideoCategoryRepositoryInterface $categoryRepo,
        private VideoRepositoryInterface $videoRepo
    ) {}

    public function index(): JsonResponse
    {
        $categories = $this->categoryRepo->rootCategories(['children']);

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function videos(string $slug, Request $request): JsonResponse
    {
        $category = $this->categoryRepo->findBySlug($slug);

        if (!$category) {
            return response()->json(['success' => false, 'error' => 'Category not found'], 404);
        }

        $query = $category->videos()
            ->with(['creator', 'categories'])
            ->where('is_published', true)
            ->where('upload_status', 'ready');

        $sortBy = $request->get('sort_by', 'published_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $videos = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category,
                'videos' => $videos->items(),
            ],
            'meta' => [
                'current_page' => $videos->currentPage(),
                'per_page' => $videos->perPage(),
                'total' => $videos->total(),
            ],
        ]);
    }
}
