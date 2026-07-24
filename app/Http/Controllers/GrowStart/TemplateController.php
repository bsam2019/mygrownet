<?php

namespace App\Http\Controllers\GrowStart;

use App\Http\Controllers\Controller;
use App\Domain\GrowStart\Repositories\TemplateRepositoryInterface;
use App\Domain\GrowStart\Repositories\JourneyRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TemplateController extends Controller
{
    public function __construct(
        private TemplateRepositoryInterface $templateRepo,
        private JourneyRepositoryInterface $journeyRepository
    ) {}

    public function index(Request $request): Response
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);
        $countryId = $journey ? $journey->getCountryId() : null;

        $templates = $this->templateRepo->findAll($countryId);

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
        $template = $this->templateRepo->findById($id);

        if (!$template) {
            abort(404);
        }

        return Inertia::render('GrowStart/Templates/Preview', [
            'template' => $template,
        ]);
    }

    public function download(Request $request, int $id)
    {
        $template = $this->templateRepo->findById($id);

        if (!$template) {
            abort(404);
        }

        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        if ($template['is_premium'] && (!$journey || !$journey->isPremium())) {
            return back()->withErrors(['access' => 'Premium subscription required.']);
        }

        $this->templateRepo->incrementDownloads($id);

        if (Storage::exists($template['file_path'])) {
            return Storage::download($template['file_path'], $template['name'] . '.' . $template['file_type']);
        }

        return back()->withErrors(['file' => 'File not found.']);
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);
        $countryId = $journey ? $journey->getCountryId() : null;

        $templates = $this->templateRepo->findAll(
            $countryId,
            $request->has('category') ? $request->category : null
        );

        return response()->json(['data' => $templates]);
    }
}