<?php

namespace App\Http\Controllers\GrowStart;

use App\Http\Controllers\Controller;
use App\Domain\GrowStart\Entities\StartupJourney;
use App\Domain\GrowStart\Repositories\JourneyRepositoryInterface;
use App\Domain\GrowStart\Repositories\StageRepositoryInterface;
use App\Domain\GrowStart\Services\JourneyProgressService;
use App\Models\GrowStart\Industry;
use App\Models\GrowStart\Country;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use DateTimeImmutable;

class JourneyController extends Controller
{
    public function __construct(
        private JourneyRepositoryInterface $journeyRepository,
        private StageRepositoryInterface $stageRepository,
        private JourneyProgressService $progressService
    ) {}

    public function onboarding(Request $request): Response
    {
        $industries = Industry::active()->ordered()->get();
        $countries = Country::active()->get();

        return Inertia::render('GrowStart/Onboarding/Index', [
            'industries' => $industries,
            'countries' => $countries,
        ]);
    }

    public function startJourney(Request $request)
    {
        $validated = $request->validate([
            'industry_id' => 'required|exists:growstart_industries,id',
            'country_id' => 'required|exists:growstart_countries,id',
            'business_name' => 'required|string|max:255',
            'business_description' => 'nullable|string|max:1000',
            'target_launch_date' => 'nullable|date|after:today',
            'province' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
        ]);

        $user = $request->user();

        // Check if user already has an active journey
        $existingJourney = $this->journeyRepository->findActiveByUserId($user->id);
        if ($existingJourney) {
            return back()->withErrors(['journey' => 'You already have an active journey.']);
        }

        // Get first stage
        $firstStage = $this->stageRepository->findFirst();
        if (!$firstStage) {
            return back()->withErrors(['stage' => 'No stages configured.']);
        }

        // Create journey
        $journey = StartupJourney::create(
            userId: $user->id,
            industryId: $validated['industry_id'],
            countryId: $validated['country_id'],
            businessName: $validated['business_name'],
            initialStageId: $firstStage->getId(),
            businessDescription: $validated['business_description'] ?? null,
            targetLaunchDate: isset($validated['target_launch_date']) 
                ? new DateTimeImmutable($validated['target_launch_date']) 
                : null,
            province: $validated['province'] ?? null,
            city: $validated['city'] ?? null
        );

        $savedJourney = $this->journeyRepository->save($journey);

        return redirect()->route('growstart.dashboard')
            ->with('success', 'Your startup journey has begun!');
    }

    public function show(Request $request): Response
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        if (!$journey) {
            return redirect()->route('growstart.onboarding');
        }

        $progress = $this->progressService->calculateProgress($journey);
        $stages = $this->stageRepository->findActive();
        
        $industry = Industry::find($journey->getIndustryId());
        $country = Country::find($journey->getCountryId());

        return Inertia::render('GrowStart/Journey/Show', [
            'journey' => $journey->toArray(),
            'progress' => $progress->toArray(),
            'industry' => $industry,
            'country' => $country,
            'stages' => $stages->map(fn($s) => $s->toArray())->toArray(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_description' => 'nullable|string|max:1000',
            'target_launch_date' => 'nullable|date',
            'province' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
        ]);

        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        if (!$journey) {
            return back()->withErrors(['journey' => 'No active journey found.']);
        }

        $journey->updateBusinessInfo(
            businessName: $validated['business_name'],
            businessDescription: $validated['business_description'] ?? null,
            province: $validated['province'] ?? null,
            city: $validated['city'] ?? null
        );

        if (isset($validated['target_launch_date'])) {
            $journey->setTargetLaunchDate(new DateTimeImmutable($validated['target_launch_date']));
        }

        $this->journeyRepository->save($journey);

        return back()->with('success', 'Journey updated successfully.');
    }

    public function pause(Request $request)
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        if (!$journey) {
            return back()->withErrors(['journey' => 'No active journey found.']);
        }

        $journey->pause();
        $this->journeyRepository->save($journey);

        return back()->with('success', 'Journey paused.');
    }

    public function resume(Request $request)
    {
        $journey = $this->journeyRepository->findByUserId($request->user()->id);

        if (!$journey || !$journey->getStatus()->isPaused()) {
            return back()->withErrors(['journey' => 'No paused journey found.']);
        }

        $journey->resume();
        $this->journeyRepository->save($journey);

        return back()->with('success', 'Journey resumed.');
    }

    public function progress(Request $request): JsonResponse
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        if (!$journey) {
            return response()->json(['error' => 'No active journey'], 404);
        }

        $progress = $this->progressService->calculateProgress($journey);
        $timeline = $this->progressService->getTimelineStatus($journey);

        return response()->json([
            'progress' => $progress->toArray(),
            'timeline' => $timeline,
        ]);
    }

    // API Methods
    public function apiShow(Request $request): JsonResponse
    {
        $journey = $this->journeyRepository->findActiveByUserId($request->user()->id);

        if (!$journey) {
            return response()->json(['data' => null]);
        }

        return response()->json(['data' => $journey->toArray()]);
    }

    public function apiStore(Request $request): JsonResponse
    {
        // Same logic as startJourney but returns JSON
        $validated = $request->validate([
            'industry_id' => 'required|exists:growstart_industries,id',
            'country_id' => 'required|exists:growstart_countries,id',
            'business_name' => 'required|string|max:255',
        ]);

        $user = $request->user();
        $firstStage = $this->stageRepository->findFirst();

        $journey = StartupJourney::create(
            userId: $user->id,
            industryId: $validated['industry_id'],
            countryId: $validated['country_id'],
            businessName: $validated['business_name'],
            initialStageId: $firstStage->getId()
        );

        $savedJourney = $this->journeyRepository->save($journey);

        return response()->json(['data' => $savedJourney->toArray()], 201);
    }

    public function apiProgress(Request $request): JsonResponse
    {
        return $this->progress($request);
    }

    public function industries(): JsonResponse
    {
        $industries = Industry::active()->ordered()->get();
        return response()->json(['data' => $industries]);
    }

    public function countries(): JsonResponse
    {
        $countries = Country::active()->get();
        return response()->json(['data' => $countries]);
    }
}
