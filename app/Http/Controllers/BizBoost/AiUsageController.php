<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\AiUsageService;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\AiUsageLogRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AiUsageController extends Controller
{
    public function __construct(
        private AiUsageService $aiUsageService,
        private BusinessService $businessService,
        private AiUsageLogRepositoryInterface $aiUsageRepo,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $filters = $request->only(['feature', 'date_from', 'date_to']);

        return Inertia::render('BizBoost/AiUsage/Index', [
            'usageLogs' => $this->aiUsageService->getUsageLogs($business->id, $filters),
            'stats' => $this->aiUsageService->getUsageStats($business->id, now()->startOfMonth()->toDateTimeString(), now()->endOfMonth()->toDateTimeString()),
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'feature' => 'required|string|in:content_generation,image_generation,hashtag_suggestion,caption_writer,smart_reply,analysis,translation,other',
            'prompt' => 'required|string|max:2000',
            'context' => 'nullable|array',
            'model' => 'nullable|string|max:100',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $result = $this->aiUsageService->generate($business->id, $request->user()->id, $validated);

        return response()->json($result);
    }
}