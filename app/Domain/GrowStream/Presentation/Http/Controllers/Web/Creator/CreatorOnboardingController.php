<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Web\Creator;

use App\Domain\GrowStream\Exceptions\CreatorNotFoundException;
use App\Domain\GrowStream\Services\CreatorProfileService;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreatorOnboardingController extends Controller
{
    public function __construct(
        private CreatorProfileService $creatorService,
        private VideoRepositoryInterface $videoRepo,
    ) {}

    public function showRegister(): Response|RedirectResponse
    {
        $profile = $this->creatorService->getProfileByUserId(auth()->id());

        if ($profile) {
            return match ($profile['status'] ?? 'pending') {
                'approved', 'suspended' => redirect()->route('growstream.creator.dashboard'),
                default => redirect()->route('growstream.creator.pending'),
            };
        }

        return Inertia::render('GrowStream/Creator/Register', [
            'agreementVersion' => (string) config('growstream.creator.agreement_version', '1.0'),
        ]);
    }

    public function storeRegistration(Request $request): RedirectResponse
    {
        $request->validate([
            'display_name' => 'required|string|max:100',
            'channel_name' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:2000',
            'website_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'agree_to_terms' => 'required|accepted',
        ]);

        try {
            $profile = $this->creatorService->applyForCreator(
                auth()->id(),
                $request->only([
                    'display_name',
                    'channel_name',
                    'bio',
                    'website_url',
                    'facebook_url',
                    'twitter_url',
                    'instagram_url',
                    'youtube_url',
                ])
            );
        } catch (CreatorNotFoundException $e) {
            return back()->with('error', 'You already have a creator application on file.');
        }

        $this->creatorService->acceptAgreement(
            (int) $profile['id'],
            (string) config('growstream.creator.agreement_version', '1.0'),
            $request->ip(),
            $request->userAgent(),
        );

        return redirect()->route('growstream.creator.pending')
            ->with('success', 'Your creator application has been submitted for review.');
    }

    public function pendingApproval(): Response
    {
        $profile = $this->creatorService->getProfileByUserId(auth()->id());

        if ($profile && ($profile['status'] ?? null) === 'approved') {
            return Inertia::render('GrowStream/Creator/PendingApproval', [
                'profile' => $profile,
                'status' => 'approved',
            ]);
        }

        return Inertia::render('GrowStream/Creator/PendingApproval', [
            'profile' => $profile,
            'status' => $profile['status'] ?? 'none',
            'rejectedReason' => $profile['rejected_reason'] ?? null,
        ]);
    }

    public function dashboard(): Response|RedirectResponse
    {
        $profile = $this->creatorService->getProfileByUserId(auth()->id());

        if (! $profile || ($profile['status'] ?? null) !== 'approved') {
            return redirect()->route('growstream.creator.register');
        }

        $user = auth()->user();
        $orgId = $user->organization_id ?? $user->id;

        $platform = \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorPlatform::firstOrCreate(
            ['organization_id' => $orgId],
            [
                'brand_name' => $user->name . ' Academy',
                'category' => 'education',
                'subdomain' => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $user->name)),
                'brand_color' => '#e2571f',
            ]
        );

        $hasUploadedContent = Video::where('organization_id', $orgId)->orWhere('creator_id', $profile['id'] ?? 0)->exists();
        $initialViewState = $hasUploadedContent ? 'established' : 'new';

        $terminologyService = app(\App\Domain\GrowStream\Services\TenantTerminologyService::class);
        $terminology = $terminologyService->getMap($platform->category);
        $allCategories = $terminologyService->getAllCategories();

        return Inertia::render('GrowStream/Creator/Dashboard', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'platform' => $platform,
            'terminology' => $terminology,
            'allCategories' => $allCategories,
            'initialViewState' => $initialViewState,
            'profile' => $profile,
            'recentVideos' => $recentVideos ?? [],
            'earningsSummary' => $earningsSummary ?? ['total_earnings' => 0, 'pending_payout' => 0],
            'watchTimeHours' => round(($totalWatchSeconds ?? 0) / 3600, 1),
        ]);
    }
}
