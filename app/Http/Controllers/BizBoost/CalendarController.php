<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\PostRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function __construct(
        private BusinessService $businessService,
        private PostRepositoryInterface $postRepo,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        $startDate = $request->start
            ? Carbon::parse($request->start)->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->end
            ? Carbon::parse($request->end)->endOfDay()
            : now()->endOfMonth();

        $posts = $this->postRepo->findByBusinessDateRange($business->id, $startDate->toDateTimeString(), $endDate->toDateTimeString());

        $weeklyThemes = DB::table('bizboost_weekly_themes')
            ->where('business_id', $business->id)
            ->whereBetween('week_start', [
                $startDate->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY),
                $endDate->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY),
            ])
            ->orderBy('week_start')
            ->get()
            ->map(fn($theme) => [
                'id' => $theme->id,
                'week_start' => $theme->week_start,
                'theme' => $theme->theme,
                'description' => $theme->description,
                'color' => $theme->color,
            ]);

        return Inertia::render('BizBoost/Calendar/Index', [
            'posts' => $posts,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'suggestedTimes' => $this->getPostingTimes($business->id, $business->industry),
            'suggestions' => $this->getMarketingCalendarSuggestions($startDate, $endDate),
            'weeklyThemes' => $weeklyThemes,
            'defaultThemes' => $this->getDefaultWeeklyThemes(),
            'business' => ['industry' => $business->industry, 'timezone' => $business->timezone],
        ]);
    }

    public function storeWeeklyTheme(Request $request)
    {
        $validated = $request->validate([
            'week_start' => 'required|date',
            'theme' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:20',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $weekStart = Carbon::parse($validated['week_start'])->startOfWeek(Carbon::MONDAY);

        DB::table('bizboost_weekly_themes')->updateOrInsert(
            ['business_id' => $business->id, 'week_start' => $weekStart->format('Y-m-d')],
            [
                'theme' => $validated['theme'],
                'description' => $validated['description'] ?? null,
                'color' => $validated['color'] ?? null,
                'updated_at' => now(),
            ]
        );

        return back()->with('success', 'Weekly theme saved successfully.');
    }

    public function destroyWeeklyTheme(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        DB::table('bizboost_weekly_themes')->where('id', $id)->where('business_id', $business->id)->delete();
        return back()->with('success', 'Weekly theme removed.');
    }

    public function events(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $startDate = Carbon::parse($request->start);
        $endDate = Carbon::parse($request->end);

        $posts = $this->postRepo->findByBusinessDateRange($business->id, $startDate->toDateTimeString(), $endDate->toDateTimeString());

        $statusColors = ['draft' => '#6b7280', 'scheduled' => '#3b82f6', 'publishing' => '#f59e0b', 'published' => '#10b981', 'failed' => '#ef4444'];

        $events = collect($posts)->map(fn($post) => [
            'id' => $post->id,
            'title' => $post->title ?? substr($post->caption, 0, 30),
            'start' => $post->scheduledAt ?? $post->publishedAt ?? $post->createdAt,
            'backgroundColor' => $statusColors[$post->status] ?? '#6b7280',
            'borderColor' => $statusColors[$post->status] ?? '#6b7280',
            'extendedProps' => ['status' => $post->status, 'platforms' => $post->platforms, 'post_type' => $post->postType],
        ]);

        return response()->json($events);
    }

    public function reschedule(Request $request, int $id)
    {
        $validated = $request->validate(['scheduled_at' => 'required|date|after:now']);
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $post = $this->postRepo->findById($business->id, $id);

        if (!$post) {
            abort(404);
        }

        if (!in_array($post->status, ['draft', 'scheduled'])) {
            return response()->json(['error' => 'Cannot reschedule a published post.'], 422);
        }

        $this->postRepo->save(new \App\Domain\BizBoost\Entities\Post(
            id: $post->id,
            businessId: $post->businessId,
            content: $post->content,
            caption: $post->caption,
            title: $post->title,
            platforms: $post->platforms,
            status: 'scheduled',
            postType: $post->postType,
            scheduledAt: $validated['scheduled_at'],
            publishedAt: $post->publishedAt,
            link: $post->link,
            callToAction: $post->callToAction,
            tags: $post->tags,
            templateId: $post->templateId,
            customTemplateId: $post->customTemplateId,
            locationId: $post->locationId,
            engagement: $post->engagement,
            mediaUrls: $post->mediaUrls,
            createdAt: $post->createdAt,
            updatedAt: null,
        ));

        return response()->json(['success' => true, 'message' => 'Post rescheduled successfully.']);
    }

    private function getPostingTimes(int $businessId, ?string $industry): array
    {
        $customTimes = DB::table('bizboost_posting_times')
            ->where('business_id', $businessId)
            ->get()
            ->keyBy('day_type');

        if ($customTimes->isNotEmpty()) {
            return $customTimes->map(fn($r) => ['day' => $r->day_type, 'times' => $r->times])->values()->all();
        }

        $industryTimes = [
            'restaurant' => [['day' => 'weekday', 'times' => ['11:00', '17:00', '19:00']], ['day' => 'weekend', 'times' => ['10:00', '12:00', '18:00']]],
            'salon' => [['day' => 'weekday', 'times' => ['09:00', '13:00', '18:00']], ['day' => 'weekend', 'times' => ['10:00', '14:00']]],
            'boutique' => [['day' => 'weekday', 'times' => ['10:00', '14:00', '19:00']], ['day' => 'weekend', 'times' => ['11:00', '15:00', '18:00']]],
            'default' => [['day' => 'weekday', 'times' => ['09:00', '12:00', '18:00']], ['day' => 'weekend', 'times' => ['10:00', '14:00', '17:00']]],
        ];

        return $industryTimes[$industry] ?? $industryTimes['default'];
    }

    public function updatePostingTimes(Request $request)
    {
        $validated = $request->validate([
            'times' => 'required|array',
            'times.*.day' => 'required|string|in:weekday,weekend,monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'times.*.times' => 'required|array',
            'times.*.times.*' => 'required|string|date_format:H:i',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        DB::table('bizboost_posting_times')->where('business_id', $business->id)->delete();
        foreach ($validated['times'] as $timeConfig) {
            DB::table('bizboost_posting_times')->insert([
                'business_id' => $business->id,
                'day_type' => $timeConfig['day'],
                'times' => $timeConfig['times'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        return back()->with('success', 'Posting times updated successfully.');
    }

    public function resetPostingTimes(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        DB::table('bizboost_posting_times')->where('business_id', $business->id)->delete();
        return back()->with('success', 'Posting times reset to defaults.');
    }

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

    private function getMarketingCalendarSuggestions(Carbon $startDate, Carbon $endDate): array
    {
        $suggestions = [];
        $current = $startDate->copy();
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
                $suggestions[] = ['date' => $current->toDateString(), 'type' => $events[$dateKey]['type'], 'name' => $events[$dateKey]['name'], 'suggestion' => $events[$dateKey]['suggestion']];
            }
            $current->addDay();
        }
        return $suggestions;
    }
}
