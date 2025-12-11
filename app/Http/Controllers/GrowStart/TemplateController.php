<?php

namespace App\Http\Controllers\GrowStart;

use App\Http\Controllers\Controller;
use App\Models\GrowStart\Template;
use App\Domain\GrowStart\Repositories\JourneyRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TemplateController extends Controller
{
    public function __construct(
        private JourneyRepositoryInterface $journeyRepository
    ) {}

    public function index(Request $request): Response
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        $query = Template::query();

        // Filter by country
        if ($journey) {
            $query->forCountry($journey->getCountryId());
        }

        $templates = $query->orderBy('name')->get();

        $categories = [
            ['value' => 'business_plan', 'label' => 'Business Plan'],
            ['value' => 'financial', 'label' => 'Financial'],
            ['value' => 'marketing', 'label' => 'Marketing'],
            ['value' => 'legal', 'label' => 'Legal'],
            ['value' => 'operations', 'label' => 'Operations'],
        ];

        return Inertia::render('GrowStart/Templates/Index', [
            'templates' => $templates,
            'categories' => $categories,
        ]);
    }

    public function show(Request $request, int $id): Response
    {
        $template = Template::findOrFail($id);

        return Inertia::render('GrowStart/Templates/Preview', [
            'template' => $template,
        ]);
    }

    public function download(Request $request, int $id)
    {
        $template = Template::findOrFail($id);
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        // Check premium access
        if ($template->is_premium && (!$journey || !$journey->isPremium())) {
            return back()->withErrors(['access' => 'Premium subscription required.']);
        }

        // Increment download count
        $template->incrementDownloads();

        // Return file download
        if (Storage::exists($template->file_path)) {
            return Storage::download($template->file_path, $template->name . '.' . $template->file_type);
        }

        return back()->withErrors(['file' => 'File not found.']);
    }

    // API Methods
    public function apiIndex(Request $request): JsonResponse
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        $query = Template::query();

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        if ($journey) {
            $query->forCountry($journey->getCountryId());
        }

        $templates = $query->orderBy('name')->get();

        return response()->json(['data' => $templates]);
    }
}
