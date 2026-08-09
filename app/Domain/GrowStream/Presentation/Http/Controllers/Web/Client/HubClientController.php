<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Web\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorPlatform;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory;
use App\Domain\GrowStream\Services\TenantTerminologyService;

class HubClientController extends Controller
{
    public function __construct(
        private TenantTerminologyService $terminologyService
    ) {}

    public function dashboard(Request $request): Response
    {
        $user = $request->user();
        
        // Resolve current platform context (subdomain hint or default)
        $domainContext = $request->attributes->get('domain_context');
        $platform = null;
        if ($domainContext && !empty($domainContext->subdomain)) {
            $platform = CreatorPlatform::where('subdomain', $domainContext->subdomain)->first();
        }
        if (!$platform) {
            $platform = CreatorPlatform::where('is_active', true)->first();
        }

        $brandName = $platform ? $platform->brand_name : 'Tuition Academy';
        $category = $platform ? $platform->category : 'education';
        $terminology = $this->terminologyService->getMap($category);

        // Fetch user enrolled videos and watch history
        $history = WatchHistory::where('user_id', $user->id)->with('video')->get();
        
        $enrolledVideos = $history->map(function ($h) {
            $v = $h->video;
            if (!$v) return null;
            return [
                'id' => $v->id,
                'title' => $v->title,
                'slug' => $v->slug,
                'description' => $v->description,
                'thumbnail_url' => $v->thumbnail_url,
                'duration' => $v->duration,
                'category' => $v->category ? $v->category->name : 'Tuition',
                'progress_percentage' => (int) $h->progress_percentage,
            ];
        })->filter()->values();

        $completedLessonsCount = $history->filter(fn($h) => $h->is_completed)->count();

        return Inertia::render('GrowStream/Client/Dashboard', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'platform' => [
                'id' => $platform ? $platform->id : 0,
                'brand_name' => $brandName,
                'subdomain' => $platform ? $platform->subdomain : '',
                'category' => $category,
            ],
            'terminology' => $terminology,
            'subscriptionActive' => $enrolledVideos->count() > 0,
            'enrolledVideos' => $enrolledVideos,
            'enrolledCount' => $enrolledVideos->count(),
            'completedLessonsCount' => $completedLessonsCount,
            'resourcesCount' => 3,
        ]);
    }
}
