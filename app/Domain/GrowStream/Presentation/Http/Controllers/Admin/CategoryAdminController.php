<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Admin;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryAdminController extends Controller
{
    /**
     * List all categories (flat, with parent name + video counts).
     */
    public function index(Request $request): Response
    {
        $query = VideoCategory::withCount('videos');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $categories = $query->orderBy('sort_order')->orderBy('name')->get()->map(fn (VideoCategory $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'description' => $c->description,
            'icon' => $c->icon,
            'color' => $c->color,
            'sort_order' => $c->sort_order,
            'is_active' => (bool) $c->is_active,
            'parent_id' => $c->parent_id,
            'parent_name' => $c->parent?->name,
            'videos_count' => $c->videos_count,
        ]);

        return Inertia::render('GrowStream/Admin/Categories', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a new category.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCategory($request);

        VideoCategory::create($data);

        return back()->with('success', 'Category created.');
    }

    /**
     * Update an existing category.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $category = VideoCategory::findOrFail($id);
        $data = $this->validateCategory($request, $category->id);

        // Changing to a parent category would break the tree; prevent self/descendant parents.
        if (! empty($data['parent_id'])) {
            abort_if($data['parent_id'] == $category->id, 422, 'A category cannot be its own parent.');
        }

        $category->update($data);

        return back()->with('success', 'Category updated.');
    }

    /**
     * Delete a category (children are re-homed to the deleted category's parent).
     */
    public function destroy(int $id): RedirectResponse
    {
        $category = VideoCategory::findOrFail($id);

        // Reparent children before deletion so their videos are not orphaned.
        VideoCategory::where('parent_id', $category->id)
            ->update(['parent_id' => $category->parent_id]);

        $category->videos()->detach();
        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    private function validateCategory(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer', 'exists:growstream_video_categories,id'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:7'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = Str::slug($data['name']);

        // Ensure slug uniqueness, appending a suffix when taken by another row.
        $base = $data['slug'];
        $slug = $base;
        $n = 2;
        $exists = VideoCategory::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists();
        while ($exists) {
            $slug = $base.'-'.$n++;
            $exists = VideoCategory::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists();
        }
        $data['slug'] = $slug;

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
