<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Admin;

use App\Domain\GrowStream\Services\SponsorshipService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SponsorshipController extends Controller
{
    public function __construct(
        private SponsorshipService $sponsorshipService,
    ) {}

    public function index(Request $request): Response
    {
        $grants = $this->sponsorshipService->paginate(
            $request->only(['status', 'creator_id']),
            $request->get('per_page', 20)
        );

        return Inertia::render('GrowStream/Admin/Sponsorship', [
            'grants' => $grants,
            'stats' => [
                'total_approved' => $this->sponsorshipService->totalApproved(),
                'total_disbursed' => $this->sponsorshipService->totalDisbursed(),
            ],
            'filters' => $request->only(['status', 'creator_id']),
        ]);
    }

    public function approve(int $id): RedirectResponse
    {
        $this->sponsorshipService->approve($id, auth()->id());

        return back()->with('success', 'Sponsorship grant approved.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|max:2000']);

        $this->sponsorshipService->reject($id, $request->reason, auth()->id());

        return back()->with('success', 'Sponsorship grant rejected.');
    }

    public function disburse(int $id): RedirectResponse
    {
        $this->sponsorshipService->disburse($id);

        return back()->with('success', 'Sponsorship funds disbursed to the creator ledger.');
    }

    public function complete(int $id): RedirectResponse
    {
        $this->sponsorshipService->complete($id);

        return back()->with('success', 'Sponsorship marked as completed.');
    }
}
