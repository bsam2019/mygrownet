<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Web\Creator;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Repositories\CreatorProfileRepositoryInterface;
use App\Domain\GrowStream\Services\SponsorshipService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreatorSponsorshipController extends Controller
{
    public function __construct(
        private SponsorshipService $sponsorshipService,
        private CreatorProfileRepositoryInterface $creatorRepo,
    ) {}

    private function requireCreator(): CreatorProfile
    {
        $creator = $this->creatorRepo->findByUserId(auth()->id());
        abort_unless($creator !== null && $creator->status === 'approved', 403);

        return $creator;
    }

    public function index(): Response
    {
        $creator = $this->requireCreator();

        return Inertia::render('GrowStream/Creator/Sponsorship', [
            'grants' => $this->sponsorshipService->forCreator($creator->id, ['creator.user']),
            'stats' => [
                'total_approved' => $this->sponsorshipService->totalApproved(),
                'total_disbursed' => $this->sponsorshipService->totalDisbursed(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $creator = $this->requireCreator();

        $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'required|string|max:5000',
            'amount' => 'required|numeric|min:1',
            'milestones' => 'nullable|array',
            'milestones.*' => 'string|max:500',
        ]);

        $this->sponsorshipService->apply(
            $creator->id,
            $request->title,
            $request->description,
            (float) $request->amount,
            $request->milestones ?? [],
        );

        return redirect()->route('growstream.creator.sponsorship.index')
            ->with('success', 'Sponsorship proposal submitted for review.');
    }
}
