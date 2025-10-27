<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StarterKitContentItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StarterKitContentController extends Controller
{
    public function index()
    {
        $items = StarterKitContentItem::query()
            ->ordered()
            ->get()
            ->groupBy('category');

        $stats = [
            'total_items' => StarterKitContentItem::count(),
            'active_items' => StarterKitContentItem::active()->count(),
            'total_value' => StarterKitContentItem::active()->sum('estimated_value'),
            'by_category' => StarterKitContentItem::active()
                ->selectRaw('category, count(*) as count, sum(estimated_value) as value')
                ->groupBy('category')
                ->get()
                ->keyBy('category'),
        ];

        return Inertia::render('Admin/StarterKit/Content', [
            'items' => $items,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:training,ebook,video,tool,library',
            'unlock_day' => 'required|integer|min:0|max:30',
            'file_path' => 'nullable|string',
            'file_type' => 'nullable|string',
            'file_size' => 'nullable|integer',
            'thumbnail' => 'nullable|string',
            'estimated_value' => 'required|integer|min:0',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $item = StarterKitContentItem::create($validated);

        return redirect()->back()->with('success', 'Content item created successfully!');
    }

    public function update(Request $request, StarterKitContentItem $item)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:training,ebook,video,tool,library',
            'unlock_day' => 'required|integer|min:0|max:30',
            'file_path' => 'nullable|string',
            'file_type' => 'nullable|string',
            'file_size' => 'nullable|integer',
            'thumbnail' => 'nullable|string',
            'estimated_value' => 'required|integer|min:0',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $item->update($validated);

        return redirect()->back()->with('success', 'Content item updated successfully!');
    }

    public function destroy(StarterKitContentItem $item)
    {
        $item->delete();

        return redirect()->back()->with('success', 'Content item deleted successfully!');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:starter_kit_content_items,id',
            'items.*.sort_order' => 'required|integer',
        ]);

        foreach ($validated['items'] as $itemData) {
            StarterKitContentItem::where('id', $itemData['id'])
                ->update(['sort_order' => $itemData['sort_order']]);
        }

        return redirect()->back()->with('success', 'Items reordered successfully!');
    }
}
