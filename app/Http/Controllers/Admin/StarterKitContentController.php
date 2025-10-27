<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\StarterKit\ContentItemModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StarterKitContentController extends Controller
{
    public function index()
    {
        $items = ContentItemModel::query()
            ->ordered()
            ->get()
            ->groupBy('category');

        $stats = [
            'total_items' => ContentItemModel::count(),
            'active_items' => ContentItemModel::active()->count(),
            'total_value' => ContentItemModel::active()->sum('estimated_value'),
            'by_category' => ContentItemModel::active()
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
            'file' => 'nullable|file|max:102400', // 100MB max
            'thumbnail' => 'nullable|image|max:2048', // 2MB max
            'estimated_value' => 'required|integer|min:0',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('starter-kit/content', $filename, 'public');
            $validated['file_path'] = $path;
            $validated['file_type'] = $file->getClientOriginalExtension();
            $validated['file_size'] = round($file->getSize() / 1024); // Convert to KB
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $thumbnailName = time() . '_thumb_' . $thumbnail->getClientOriginalName();
            $thumbnailPath = $thumbnail->storeAs('starter-kit/thumbnails', $thumbnailName, 'public');
            $validated['thumbnail'] = $thumbnailPath;
        }

        $item = ContentItemModel::create($validated);

        return redirect()->back()->with('success', 'Content item created successfully!');
    }

    public function update(Request $request, ContentItemModel $item)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:training,ebook,video,tool,library',
            'unlock_day' => 'required|integer|min:0|max:30',
            'file' => 'nullable|file|max:102400', // 100MB max
            'thumbnail_file' => 'nullable|image|max:2048', // 2MB max
            'estimated_value' => 'required|integer|min:0',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        // Handle file upload
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($item->file_path && \Storage::disk('public')->exists($item->file_path)) {
                \Storage::disk('public')->delete($item->file_path);
            }
            
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('starter-kit/content', $filename, 'public');
            $validated['file_path'] = $path;
            $validated['file_type'] = $file->getClientOriginalExtension();
            $validated['file_size'] = round($file->getSize() / 1024); // Convert to KB
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail_file')) {
            // Delete old thumbnail if exists
            if ($item->thumbnail && \Storage::disk('public')->exists($item->thumbnail)) {
                \Storage::disk('public')->delete($item->thumbnail);
            }
            
            $thumbnail = $request->file('thumbnail_file');
            $thumbnailName = time() . '_thumb_' . $thumbnail->getClientOriginalName();
            $thumbnailPath = $thumbnail->storeAs('starter-kit/thumbnails', $thumbnailName, 'public');
            $validated['thumbnail'] = $thumbnailPath;
        }

        $item->update($validated);

        return redirect()->back()->with('success', 'Content item updated successfully!');
    }

    public function destroy(ContentItemModel $item)
    {
        // Delete associated files
        if ($item->file_path && \Storage::disk('public')->exists($item->file_path)) {
            \Storage::disk('public')->delete($item->file_path);
        }
        
        if ($item->thumbnail && \Storage::disk('public')->exists($item->thumbnail)) {
            \Storage::disk('public')->delete($item->thumbnail);
        }
        
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
            ContentItemModel::where('id', $itemData['id'])
                ->update(['sort_order' => $itemData['sort_order']]);
        }

        return redirect()->back()->with('success', 'Items reordered successfully!');
    }
}
