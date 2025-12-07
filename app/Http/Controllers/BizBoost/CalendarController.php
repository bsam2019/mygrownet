<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostWeeklyThemeModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostPostingTimeModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request): Response
    {
        $business = $this->getBusiness($request);
        
        // Get date range from request or default to current month
        $startDate = $request->start 
            ? Carbon::parse($request->start)->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->end 
            ? Carbon::parse($request->end)->endOfDay()
            : now()->endOfMonth();

        // Get posts within date range
        $posts = $business->posts()
            ->with('media')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('scheduled_at', [$startDate, $endDate])
                    ->orWhereBetween('published_at', [$startDate, $endDate])
                    ->orWhereBetween('created_at', [$startDate, $endDate]);
            })
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title ?? substr($post->caption, 0, 50) . '...',
                    'caption' => $post->caption,
                    'status' => $post->status,
                    'date' => $post->scheduled_at ?? $post->published_at ?? $post->created_at,
                    'platform_targets' => $post->platform_targets ?? [],
                    'post_type' => $post->post_type,
                    'has_media' => $post->media->count() > 0,
                    'media_count' => $post->media->count(),
                    'thumbnail' => $post->media->first()?->path,
                ];
            });

        // Get posting times (custom or default based on industry)
        $suggestedTimes = $this->getPostingTimes($business);

        // Get marketing calendar suggestions
        $suggestions = $this->getMarketingCalendarSuggestions($startDate, $endDate);

        // Get weekly themes for the date range (extend range to include all weeks that touch the month)
        $weeklyThemes = BizBoostWeeklyThemeModel::where('business_id', $business->id)
            ->whereBetween('week_start', [
                $startDate->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY),
                $endDate->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY)
            ])
            ->orderBy('week_start')
            ->get()
            ->map(fn($theme) => [
                'id' => $theme->id,
                'week_start' => $theme->week_start->format('Y-m-d'),
                'theme' => $theme->theme,
                'description' => $theme->description,
                'color' => $theme->color,
            ]);

        // Get default theme suggestions
        $defaultThemes = $this->getDefaultWeeklyThemes();

        return Inertia::render('BizBoost/Calendar/Index', [
            'posts' => $posts,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'suggestedTimes' => $suggestedTimes,
            'suggestions' => $suggestions,
            'weeklyThemes' => $weeklyThemes,
            'defaultThemes' => $defaultThemes,
            'business' => [
                'industry' => $business->industry,
                'timezone' => $business->timezone,
            ],
        ]);
    }

    /**
     * Store or update a weekly theme.
     */
    public function storeWeeklyTheme(Request $request)
    {
        $validated = $request->validate([
            'week_start' => 'required|date',
            'theme' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:20',
        ]);

        $business = $this->getBusiness($request);
        
        // Ensure week_start is a Monday (Carbon uses Monday as start of week by default)
        $weekStart = Carbon::parse($validated['week_start'])->startOfWeek(Carbon::MONDAY);

        $theme = BizBoostWeeklyThemeModel::updateOrCreate(
            [
                'business_id' => $business->id,
                'week_start' => $weekStart->format('Y-m-d'),
            ],
            [
                'theme' => $validated['theme'],
                'description' => $validated['description'] ?? null,
                'color' => $validated['color'] ?? null,
            ]
        );

        return back()->with('success', 'Weekly theme saved successfully.');
    }

    /**
     * Delete a weekly theme.
     */
    public function destroyWeeklyTheme(Request $request, int $id)
    {
        $business = $this->getBusiness($request);
        
        $theme = BizBoostWeeklyThemeModel::where('business_id', $business->id)
            ->findOrFail($id);
        
        $theme->delete();

        return back()->with('success', 'Weekly theme removed.');
    }

    /**
     * Get default weekly theme suggestions.
     */
    private function getDefaultWeeklyThemes(): array
    {
        return [
            ['theme' => 'Product Spotlight', 'description' => 'Feature your best products or services'],
            ['theme' => 'Customer Appreciation', 'description' => 'Highlight customer stories and testimonials'],
            ['theme' => 'Behind the Scenes', 'description' => 'Show your team and processes'],
            ['theme' => 'Tips & Education', 'description' => 'Share helpful advice related to your industry'],
            ['theme' => 'Promotions Week', 'description' => 'Special offers and discounts'],
            ['theme' => 'Community Focus', 'description' => 'Local events and community involvement'],
            ['theme' => 'New Arrivals', 'description' => 'Showcase new products or services'],
            ['theme' => 'Throwback Week', 'description' => 'Share your business journey and milestones'],
        ];
    }

    public function events(Request $request)
    {
        $business = $this->getBusiness($request);
        
        $startDate = Carbon::parse($request->start);
        $endDate = Carbon::parse($request->end);

        $posts = $business->posts()
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('scheduled_at', [$startDate, $endDate])
                    ->orWhereBetween('published_at', [$startDate, $endDate]);
            })
            ->get()
            ->map(function ($post) {
                $statusColors = [
                    'draft' => '#6b7280',
                    'scheduled' => '#3b82f6',
                    'publishing' => '#f59e0b',
                    'published' => '#10b981',
                    'failed' => '#ef4444',
                ];

                return [
                    'id' => $post->id,
                    'title' => $post->title ?? substr($post->caption, 0, 30),
                    'start' => ($post->scheduled_at ?? $post->published_at)->toIso8601String(),
                    'backgroundColor' => $statusColors[$post->status] ?? '#6b7280',
                    'borderColor' => $statusColors[$post->status] ?? '#6b7280',
                    'extendedProps' => [
                        'status' => $post->status,
                        'platforms' => $post->platform_targets,
                        'post_type' => $post->post_type,
                    ],
                ];
            });

        return response()->json($posts);
    }

    public function reschedule(Request $request, int $id)
    {
        $validated = $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);

        $business = $this->getBusiness($request);
        $post = $business->posts()->findOrFail($id);

        if (!in_array($post->status, ['draft', 'scheduled'])) {
            return response()->json([
                'error' => 'Cannot reschedule a published post.',
            ], 422);
        }

        $post->update([
            'scheduled_at' => $validated['scheduled_at'],
            'status' => 'scheduled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post rescheduled successfully.',
            'post' => $post,
        ]);
    }

    /**
     * Get posting times for a business (custom or default).
     */
    private function getPostingTimes(BizBoostBusinessModel $business): array
    {
        // Check for custom posting times
        $customTimes = BizBoostPostingTimeModel::where('business_id', $business->id)
            ->get()
            ->keyBy('day_type');

        if ($customTimes->isNotEmpty()) {
            $result = [];
            foreach ($customTimes as $dayType => $record) {
                $result[] = [
                    'day' => $dayType,
                    'times' => $record->times,
                ];
            }
            return $result;
        }

        // Return industry-specific defaults
        return $this->getDefaultPostingTimes($business->industry);
    }

    /**
     * Get default posting times based on industry.
     */
    private function getDefaultPostingTimes(string $industry = null): array
    {
        // Industry-specific best posting times (Zambia timezone)
        $industryTimes = [
            'restaurant' => [
                ['day' => 'weekday', 'times' => ['11:00', '17:00', '19:00']],
                ['day' => 'weekend', 'times' => ['10:00', '12:00', '18:00']],
            ],
            'salon' => [
                ['day' => 'weekday', 'times' => ['09:00', '13:00', '18:00']],
                ['day' => 'weekend', 'times' => ['10:00', '14:00']],
            ],
            'boutique' => [
                ['day' => 'weekday', 'times' => ['10:00', '14:00', '19:00']],
                ['day' => 'weekend', 'times' => ['11:00', '15:00', '18:00']],
            ],
            'default' => [
                ['day' => 'weekday', 'times' => ['09:00', '12:00', '18:00']],
                ['day' => 'weekend', 'times' => ['10:00', '14:00', '17:00']],
            ],
        ];

        return $industryTimes[$industry] ?? $industryTimes['default'];
    }

    /**
     * Update posting times.
     */
    public function updatePostingTimes(Request $request)
    {
        $validated = $request->validate([
            'times' => 'required|array',
            'times.*.day' => 'required|string|in:weekday,weekend,monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'times.*.times' => 'required|array',
            'times.*.times.*' => 'required|string|date_format:H:i',
        ]);

        $business = $this->getBusiness($request);

        // Delete existing custom times
        BizBoostPostingTimeModel::where('business_id', $business->id)->delete();

        // Insert new times
        foreach ($validated['times'] as $timeConfig) {
            BizBoostPostingTimeModel::create([
                'business_id' => $business->id,
                'day_type' => $timeConfig['day'],
                'times' => $timeConfig['times'],
            ]);
        }

        return back()->with('success', 'Posting times updated successfully.');
    }

    /**
     * Reset posting times to defaults.
     */
    public function resetPostingTimes(Request $request)
    {
        $business = $this->getBusiness($request);
        
        BizBoostPostingTimeModel::where('business_id', $business->id)->delete();

        return back()->with('success', 'Posting times reset to defaults.');
    }

    private function getMarketingCalendarSuggestions(Carbon $startDate, Carbon $endDate): array
    {
        $suggestions = [];
        $current = $startDate->copy();

        // Zambian holidays and events
        $events = [
            '01-01' => ['name' => "New Year's Day", 'type' => 'holiday', 'suggestion' => 'New year promotions, fresh start messaging'],
            '03-08' => ['name' => "International Women's Day", 'type' => 'awareness', 'suggestion' => 'Celebrate women customers, special offers'],
            '03-12' => ['name' => 'Youth Day', 'type' => 'holiday', 'suggestion' => 'Youth-focused promotions'],
            '05-01' => ['name' => 'Labour Day', 'type' => 'holiday', 'suggestion' => 'Worker appreciation, rest day messaging'],
            '05-25' => ['name' => 'Africa Day', 'type' => 'awareness', 'suggestion' => 'African pride, local products'],
            '07-04' => ['name' => 'Heroes Day', 'type' => 'holiday', 'suggestion' => 'Patriotic content, national pride'],
            '07-05' => ['name' => 'Unity Day', 'type' => 'holiday', 'suggestion' => 'Community, togetherness messaging'],
            '08-01' => ['name' => "Farmers' Day", 'type' => 'holiday', 'suggestion' => 'Agricultural appreciation, local produce'],
            '10-18' => ['name' => 'National Prayer Day', 'type' => 'holiday', 'suggestion' => 'Spiritual, community content'],
            '10-24' => ['name' => 'Independence Day', 'type' => 'holiday', 'suggestion' => 'Patriotic promotions, Zambian pride'],
            '12-25' => ['name' => 'Christmas Day', 'type' => 'holiday', 'suggestion' => 'Holiday promotions, gift ideas'],
        ];

        while ($current <= $endDate) {
            $dateKey = $current->format('m-d');
            
            if (isset($events[$dateKey])) {
                $suggestions[] = [
                    'date' => $current->toDateString(),
                    'type' => $events[$dateKey]['type'],
                    'name' => $events[$dateKey]['name'],
                    'suggestion' => $events[$dateKey]['suggestion'],
                ];
            }

            $current->addDay();
        }

        return $suggestions;
    }

    private function getBusiness(Request $request): BizBoostBusinessModel
    {
        return BizBoostBusinessModel::where('user_id', $request->user()->id)->firstOrFail();
    }
}
