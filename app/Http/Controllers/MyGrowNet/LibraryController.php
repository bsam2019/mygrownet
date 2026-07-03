<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Application\Library\UseCases\AccessLibraryUseCase;
use App\Application\Library\UseCases\ViewResourceUseCase;
use App\Http\Controllers\Controller;
use App\Models\LibraryResource;
use App\Models\LibraryResourceAccess;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LibraryController extends Controller
{
    public function __construct(
        private readonly AccessLibraryUseCase $accessLibraryUseCase,
        private readonly ViewResourceUseCase $viewResourceUseCase
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        
        // Use the Access Library Use Case
        $result = $this->accessLibraryUseCase->execute($user);
        
        if (!$result['hasAccess']) {
            if (!$user->has_starter_kit) {
                return redirect()->route('mygrownet.starter-kit.show')
                    ->with('error', $result['reason']);
            }
            
            return redirect()->route('mygrownet.membership.show')
                ->with('error', $result['reason']);
        }

        $category = $request->get('category');
        $type = $request->get('type');
        $search = $request->get('search');

        $query = LibraryResourceModel::active()->ordered();

        if ($category) {
            $query->byCategory($category);
        }

        if ($type) {
            $query->byType($type);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
            });
        }

        $resources = $query->get()->groupBy('category');
        $featured = LibraryResourceModel::active()->featured()->ordered()->limit(6)->get();

        // Get user's access history
        $recentlyAccessed = LibraryResourceAccess::where('user_id', $user->id)
            ->with('resource')
            ->latest('accessed_at')
            ->limit(10)
            ->get()
            ->pluck('resource')
            ->filter();

        $stats = [
            'total_resources' => LibraryResourceModel::active()->count(),
            'accessed_count' => LibraryResourceAccess::where('user_id', $user->id)->distinct('library_resource_id')->count(),
            'completed_count' => LibraryResourceAccess::where('user_id', $user->id)->where('completed', true)->distinct('library_resource_id')->count(),
        ];

        return Inertia::render('GrowNet/Library', [
            'resources' => $resources,
            'featured' => $featured,
            'recentlyAccessed' => $recentlyAccessed,
            'stats' => $stats,
            'filters' => [
                'category' => $category,
                'type' => $type,
                'search' => $search,
            ],
        ]);
    }

    public function show(Request $request, LibraryResource $resource)
    {
        $user = $request->user();
        
        // Check if user has library access
        if (!$user->hasLibraryAccess()) {
            if (!$user->has_starter_kit) {
                return redirect()->route('mygrownet.starter-kit.show')
                    ->with('error', 'Purchase the Starter Kit to unlock the Resource Library.');
            }
            
            return redirect()->route('mygrownet.membership.show')
                ->with('error', 'Your 30-day free library access has expired. Activate your monthly subscription to continue.');
        }

        // Track access
        LibraryResourceAccess::create([
            'user_id' => $user->id,
            'library_resource_id' => $resource->id,
            'accessed_at' => now(),
        ]);

        // Increment view count
        $resource->incrementViewCount();

        // Get related resources
        $related = LibraryResourceModel::active()
            ->where('category', $resource->category)
            ->where('id', '!=', $resource->id)
            ->ordered()
            ->limit(4)
            ->get();

        return Inertia::render('GrowNet/LibraryResource', [
            'resource' => $resource,
            'related' => $related,
        ]);
    }

    public function markCompleted(Request $request, LibraryResource $resource)
    {
        $user = $request->user();
        
        LibraryResourceAccess::updateOrCreate(
            [
                'user_id' => $user->id,
                'library_resource_id' => $resource->id,
            ],
            [
                'completed' => true,
                'accessed_at' => now(),
            ]
        );

        return back()->with('success', 'Resource marked as completed!');
    }
}
