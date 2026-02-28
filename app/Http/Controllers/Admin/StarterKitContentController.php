<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\StarterKit\ContentItemModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class StarterKitContentController extends Controller
{
    /**
     * Display list of all content items
     */
    public function index()
    {
        $contentItems = ContentItemModel::orderBy('sort_order')
            ->orderBy('category')
            ->orderBy('title')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'category' => $item->category,
                    'category_label' => $item->category_label,
                    'tier_restriction' => $item->tier_restriction,
                    'unlock_day' => $item->unlock_day,
                    'estimated_value' => $item->estimated_value,
                    'download_count' => $item->download_count,
                    'is_downloadable' => $item->is_downloadable,
                    'is_active' => $item->is_active,
                    'has_file' => !empty($item->file_path) || !empty($item->file_url),
                    'file_type' => $item->file_type,
                    'file_size' => $item->file_size,
                    'sort_order' => $item->sort_order,
                ];
            });

        return Inertia::render('Admin/StarterKitContent/Index', [
            'contentItems' => $contentItems,
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $tiers = \App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitTierConfig::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($tier) => [
                'key' => $tier->tier_key,
                'name' => $tier->tier_name,
                'price' => $tier->price,
            ]);

        return Inertia::render('Admin/StarterKitContent/Form', [
            'categories' => ['training', 'ebook', 'video', 'tool', 'library'],
            'tiers' => $tiers,
        ]);
    }

    /**
     * Store new content item
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:training,ebook,video,tool,library',
            'tier_restriction' => 'required|in:all,premium',
            'unlock_day' => 'required|integer|min:0|max:30',
            'estimated_value' => 'required|integer|min:0',
            'is_downloadable' => 'boolean',
            'is_active' => 'boolean',
            'file' => 'nullable|file|max:102400', // 100MB max
            'file_url' => 'nullable|url',
            'thumbnail' => 'nullable|image|max:2048', // 2MB max
        ]);

        // Handle file upload
        $filePath = null;
        $fileType = null;
        $fileSize = null;
        $originalFileName = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalFileName = $file->getClientOriginalName();
            
            // Create a safe filename while preserving the original name
            $timestamp = time();
            $safeName = $timestamp . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalFileName);
            
            // Store with the safe filename
            $filePath = $file->storeAs(
                'starter-kit/' . $validated['category'],
                $safeName,
                's3'
            );
            
            $fileType = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();
        }

        // Handle thumbnail upload
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $thumbnailPath = $thumbnail->store('starter-kit/thumbnails', 'public');
        }

        // Get next sort order
        $maxSortOrder = ContentItemModel::max('sort_order') ?? 0;

        $content = ContentItemModel::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'tier_restriction' => $validated['tier_restriction'],
            'unlock_day' => $validated['unlock_day'],
            'estimated_value' => $validated['estimated_value'],
            'is_downloadable' => $validated['is_downloadable'] ?? true,
            'is_active' => $validated['is_active'] ?? true,
            'file_path' => $filePath,
            'original_filename' => $originalFileName,
            'file_url' => $validated['file_url'] ?? null,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'thumbnail' => $thumbnailPath,
            'sort_order' => $maxSortOrder + 1,
        ]);

        return redirect()
            ->route('admin.starter-kit-content.index')
            ->with('success', 'Content item created successfully!');
    }

    /**
     * Show edit form
     */
    public function edit(int $id)
    {
        $content = ContentItemModel::findOrFail($id);

        $tiers = \App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitTierConfig::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($tier) => [
                'key' => $tier->tier_key,
                'name' => $tier->tier_name,
                'price' => $tier->price,
            ]);

        return Inertia::render('Admin/StarterKitContent/Form', [
            'content' => [
                'id' => $content->id,
                'title' => $content->title,
                'description' => $content->description,
                'category' => $content->category,
                'tier_restriction' => $content->tier_restriction,
                'unlock_day' => $content->unlock_day,
                'estimated_value' => $content->estimated_value,
                'is_downloadable' => $content->is_downloadable,
                'is_active' => $content->is_active,
                'file_url' => $content->file_url,
                'has_file' => !empty($content->file_path),
                'file_type' => $content->file_type,
                'file_size' => $content->file_size,
                'thumbnail' => $content->thumbnail,
                'sort_order' => $content->sort_order,
            ],
            'categories' => ['training', 'ebook', 'video', 'tool', 'library'],
            'tiers' => $tiers,
        ]);
    }

    /**
     * Update content item
     */
    public function update(Request $request, int $id)
    {
        $content = ContentItemModel::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:training,ebook,video,tool,library',
            'tier_restriction' => 'required|in:all,premium',
            'unlock_day' => 'required|integer|min:0|max:30',
            'estimated_value' => 'required|integer|min:0',
            'is_downloadable' => 'boolean',
            'is_active' => 'boolean',
            'file' => 'nullable|file|max:102400',
            'file_url' => 'nullable|url',
            'thumbnail' => 'nullable|image|max:2048',
            'remove_file' => 'boolean',
        ]);

        // Handle file replacement
        if ($request->hasFile('file')) {
            // Delete old file from S3
            if ($content->file_path && Storage::disk('s3')->exists($content->file_path)) {
                Storage::disk('s3')->delete($content->file_path);
            }

            $file = $request->file('file');
            $originalFileName = $file->getClientOriginalName();
            
            // Create a safe filename while preserving the original name
            $timestamp = time();
            $safeName = $timestamp . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalFileName);
            
            // Store with the safe filename
            $validated['file_path'] = $file->storeAs(
                'starter-kit/' . $validated['category'],
                $safeName,
                's3'
            );
            $validated['original_filename'] = $originalFileName;
            $validated['file_type'] = $file->getClientOriginalExtension();
            $validated['file_size'] = $file->getSize();
        } elseif ($request->input('remove_file')) {
            // Delete file if requested
            if ($content->file_path && Storage::disk('s3')->exists($content->file_path)) {
                Storage::disk('s3')->delete($content->file_path);
            }
            $validated['file_path'] = null;
            $validated['original_filename'] = null;
            $validated['file_type'] = null;
            $validated['file_size'] = null;
        }

        // Handle thumbnail replacement
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($content->thumbnail && Storage::disk('public')->exists($content->thumbnail)) {
                Storage::disk('public')->delete($content->thumbnail);
            }

            $thumbnail = $request->file('thumbnail');
            $validated['thumbnail'] = $thumbnail->store('starter-kit/thumbnails', 'public');
        }

        $content->update($validated);

        return redirect()
            ->route('admin.starter-kit-content.index')
            ->with('success', 'Content item updated successfully!');
    }

    /**
     * Delete content item
     */
    public function destroy(int $id)
    {
        $content = ContentItemModel::findOrFail($id);

        // Delete associated files from S3
        if ($content->file_path && Storage::disk('s3')->exists($content->file_path)) {
            Storage::disk('s3')->delete($content->file_path);
        }

        if ($content->thumbnail && Storage::disk('public')->exists($content->thumbnail)) {
            Storage::disk('public')->delete($content->thumbnail);
        }

        $content->delete();

        return redirect()
            ->route('admin.starter-kit-content.index')
            ->with('success', 'Content item deleted successfully!');
    }

    /**
     * Reorder content items
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:starter_kit_content_items,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            ContentItemModel::where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['message' => 'Order updated successfully']);
    }
}
