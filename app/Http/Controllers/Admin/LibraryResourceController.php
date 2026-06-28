<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Library\LibraryResourceModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LibraryResourceController extends Controller
{
    public function index()
    {
        $resources = LibraryResourceModel::query()
            ->ordered()
            ->get()
            ->groupBy('category');

        $stats = [
            'total_resources' => LibraryResourceModel::count(),
            'active_resources' => LibraryResourceModel::active()->count(),
            'featured_resources' => LibraryResourceModel::featured()->count(),
            'total_views' => LibraryResourceModel::sum('view_count'),
            'by_type' => LibraryResourceModel::active()
                ->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->get()
                ->keyBy('type'),
            'by_category' => LibraryResourceModel::active()
                ->selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->get()
                ->keyBy('category'),
        ];

        return Inertia::render('Admin/Library/Resources', [
            'resources' => $resources,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:pdf,video,article,course,tool,template',
            'category' => 'required|in:business,marketing,finance,leadership,personal_development,network_building',
            'resource_url' => 'required|string',
            'thumbnail' => 'nullable|image|max:2048',
            'author' => 'nullable|string|max:255',
            'duration_minutes' => 'nullable|integer|min:0',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'is_external' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $thumbnailName = time() . '_' . $thumbnail->getClientOriginalName();
            $thumbnailPath = $thumbnail->storeAs('library/thumbnails', $thumbnailName, 'public');
            $validated['thumbnail'] = $thumbnailPath;
        }

        LibraryResourceModel::create($validated);

        return redirect()->back()->with('success', 'Resource added successfully!');
    }

    public function update(Request $request, LibraryResourceModel $resource)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:pdf,video,article,course,tool,template',
            'category' => 'required|in:business,marketing,finance,leadership,personal_development,network_building',
            'resource_url' => 'required|string',
            'thumbnail_file' => 'nullable|image|max:2048',
            'author' => 'nullable|string|max:255',
            'duration_minutes' => 'nullable|integer|min:0',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'is_external' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail_file')) {
            // Delete old thumbnail if exists
            if ($resource->thumbnail && \Storage::disk('public')->exists($resource->thumbnail)) {
                \Storage::disk('public')->delete($resource->thumbnail);
            }
            
            $thumbnail = $request->file('thumbnail_file');
            $thumbnailName = time() . '_' . $thumbnail->getClientOriginalName();
            $thumbnailPath = $thumbnail->storeAs('library/thumbnails', $thumbnailName, 'public');
            $validated['thumbnail'] = $thumbnailPath;
        }

        $resource->update($validated);

        return redirect()->back()->with('success', 'Resource updated successfully!');
    }

    public function destroy(LibraryResourceModel $resource)
    {
        // Delete thumbnail if exists
        if ($resource->thumbnail && \Storage::disk('public')->exists($resource->thumbnail)) {
            \Storage::disk('public')->delete($resource->thumbnail);
        }
        
        $resource->delete();

        return redirect()->back()->with('success', 'Resource deleted successfully!');
    }
}
