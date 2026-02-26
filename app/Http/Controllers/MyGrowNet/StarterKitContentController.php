<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\StarterKit\ContentItemModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StarterKitContentController extends Controller
{
    /**
     * Display user's available content library
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Check if user has starter kit
        if (!$user->has_starter_kit) {
            return redirect()
                ->route('mygrownet.starter-kit.purchase')
                ->with('error', 'You need to purchase a starter kit to access this content.');
        }
        
        // Get all content items user has access to
        $query = ContentItemModel::active()->ordered();
        
        // Filter by tier - basic users only see 'all' tier content
        if ($user->starter_kit_tier !== 'premium') {
            $query->where('tier_restriction', 'all');
        }
        
        // Get content grouped by category
        $contentItems = $query->get()->groupBy('category');
        
        // Get user's access history
        $accessHistory = DB::table('starter_kit_content_access')
            ->where('user_id', $user->id)
            ->pluck('last_accessed_at', 'content_id');
        
        // Format content with access info
        $formattedContent = [];
        foreach ($contentItems as $category => $items) {
            $formattedContent[$category] = $items->map(function ($item) use ($accessHistory) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'category' => $item->category,
                    'category_label' => $item->category_label,
                    'tier_restriction' => $item->tier_restriction,
                    'estimated_value' => $item->estimated_value,
                    'file_type' => $item->file_type,
                    'file_size' => $item->file_size,
                    'is_downloadable' => $item->is_downloadable,
                    'download_count' => $item->download_count,
                    'thumbnail' => $item->thumbnail,
                    'last_accessed' => $accessHistory[$item->id] ?? null,
                    'has_file' => !empty($item->file_path) || !empty($item->file_url),
                ];
            });
        }
        
        return Inertia::render('GrowNet/StarterKitContent', [
            'contentItems' => $formattedContent,
            'userTier' => $user->starter_kit_tier,
            'totalValue' => $query->sum('estimated_value'),
        ]);
    }
    
    /**
     * Show specific content item details
     */
    public function show(Request $request, int $id)
    {
        $user = $request->user();
        $content = ContentItemModel::findOrFail($id);
        
        // Check tier access
        if ($content->tier_restriction === 'premium' && $user->starter_kit_tier !== 'premium') {
            return redirect()
                ->route('mygrownet.starter-kit.upgrade')
                ->with('info', 'This content is available for Premium tier members.');
        }
        
        // Track access
        $this->trackAccess($user->id, $content->id);
        
        return Inertia::render('GrowNet/StarterKitContentView', [
            'content' => [
                'id' => $content->id,
                'title' => $content->title,
                'description' => $content->description,
                'category' => $content->category,
                'category_label' => $content->category_label,
                'tier_restriction' => $content->tier_restriction,
                'estimated_value' => $content->estimated_value,
                'file_type' => $content->file_type,
                'file_size' => $content->file_size,
                'is_downloadable' => $content->is_downloadable,
                'download_count' => $content->download_count,
                'thumbnail' => $content->thumbnail,
                'has_file' => !empty($content->file_path) || !empty($content->file_url),
            ],
        ]);
    }
    
    /**
     * Download content file
     */
    public function download(Request $request, int $id)
    {
        $user = $request->user();
        $content = ContentItemModel::findOrFail($id);
        
        // Check tier access
        if ($content->tier_restriction === 'premium' && $user->starter_kit_tier !== 'premium') {
            abort(403, 'This content is available for Premium tier members only.');
        }
        
        // Check if downloadable
        if (!$content->is_downloadable) {
            abort(403, 'This content is not available for download.');
        }
        
        // Check if file exists
        if (empty($content->file_path)) {
            abort(404, 'File not found.');
        }
        
        if (!Storage::exists($content->file_path)) {
            abort(404, 'File not found on server.');
        }
        
        // Track download
        $this->trackDownload($user->id, $content->id);
        
        // Increment download count
        $content->increment('download_count');
        
        // Generate filename with user watermark
        $extension = pathinfo($content->file_path, PATHINFO_EXTENSION);
        $filename = str_replace(' ', '_', $content->title) . '_' . $user->id . '.' . $extension;
        
        return Storage::download($content->file_path, $filename);
    }
    
    /**
     * Stream video content
     */
    public function stream(Request $request, int $id): StreamedResponse
    {
        $user = $request->user();
        $content = ContentItemModel::findOrFail($id);
        
        // Check tier access
        if ($content->tier_restriction === 'premium' && $user->starter_kit_tier !== 'premium') {
            abort(403, 'This content is available for Premium tier members only.');
        }
        
        // Check if file exists
        if (empty($content->file_path)) {
            abort(404, 'File not found.');
        }
        
        if (!Storage::exists($content->file_path)) {
            abort(404, 'File not found on server.');
        }
        
        // Track access
        $this->trackAccess($user->id, $content->id);
        
        $path = Storage::path($content->file_path);
        $stream = fopen($path, 'r');
        
        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => Storage::mimeType($content->file_path),
            'Content-Length' => Storage::size($content->file_path),
            'Accept-Ranges' => 'bytes',
        ]);
    }
    
    /**
     * Track content access
     */
    protected function trackAccess(int $userId, int $contentId): void
    {
        DB::table('starter_kit_content_access')->updateOrInsert(
            [
                'user_id' => $userId,
                'content_id' => $contentId,
            ],
            [
                'access_count' => DB::raw('access_count + 1'),
                'last_accessed_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
    
    /**
     * Track content download
     */
    protected function trackDownload(int $userId, int $contentId): void
    {
        DB::table('starter_kit_content_access')->updateOrInsert(
            [
                'user_id' => $userId,
                'content_id' => $contentId,
            ],
            [
                'download_count' => DB::raw('download_count + 1'),
                'last_downloaded_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
