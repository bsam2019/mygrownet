<?php

namespace App\Http\Controllers\LifePlus;

use App\Http\Controllers\Controller;
use App\Domain\LifePlus\Services\GigService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GigController extends Controller
{
    public function __construct(protected GigService $gigService) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'category', 'location', 'search']);

        return Inertia::render('LifePlus/Community/Gigs/Index', [
            'gigs' => $this->gigService->getGigs($filters),
            'categories' => $this->gigService->getCategories(),
            'filters' => $filters,
        ]);
    }

    public function show(int $id)
    {
        $gig = $this->gigService->getGig($id);

        if (!$gig) {
            return redirect()->route('lifeplus.gigs.index')
                ->with('error', 'Gig not found');
        }

        return Inertia::render('LifePlus/Community/Gigs/Show', [
            'gig' => $gig,
        ]);
    }

    public function create()
    {
        return Inertia::render('LifePlus/Community/Gigs/Create', [
            'categories' => $this->gigService->getCategories(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'category' => 'nullable|string|max:50',
            'payment_amount' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $gig = $this->gigService->createGig(auth()->id(), $validated);

        if ($request->wantsJson()) {
            return response()->json($gig, 201);
        }

        return redirect()->route('lifeplus.gigs.index')
            ->with('success', 'Gig posted successfully');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'category' => 'nullable|string|max:50',
            'payment_amount' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|in:open,cancelled',
        ]);

        $gig = $this->gigService->updateGig($id, auth()->id(), $validated);

        if (!$gig) {
            return response()->json(['error' => 'Gig not found'], 404);
        }

        if ($request->wantsJson()) {
            return response()->json($gig);
        }

        return back()->with('success', 'Gig updated');
    }

    public function destroy(int $id)
    {
        $deleted = $this->gigService->deleteGig($id, auth()->id());

        if (!$deleted) {
            return response()->json(['error' => 'Gig not found'], 404);
        }

        return redirect()->route('lifeplus.gigs.index')
            ->with('success', 'Gig deleted');
    }

    public function apply(Request $request, int $id)
    {
        $validated = $request->validate([
            'message' => 'nullable|string|max:500',
        ]);

        $result = $this->gigService->applyForGig($id, auth()->id(), $validated['message'] ?? null);

        if (!$result) {
            return response()->json(['error' => 'Cannot apply for this gig'], 422);
        }

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', 'Application submitted');
    }

    public function assign(Request $request, int $id)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:users,id',
        ]);

        $gig = $this->gigService->assignGig($id, auth()->id(), $validated['worker_id']);

        if (!$gig) {
            return response()->json(['error' => 'Cannot assign this gig'], 422);
        }

        if ($request->wantsJson()) {
            return response()->json($gig);
        }

        return back()->with('success', 'Worker assigned');
    }

    public function complete(int $id)
    {
        $gig = $this->gigService->completeGig($id, auth()->id());

        if (!$gig) {
            return response()->json(['error' => 'Cannot complete this gig'], 422);
        }

        return back()->with('success', 'Gig marked as completed');
    }

    public function myGigs()
    {
        return Inertia::render('LifePlus/Community/Gigs/MyGigs', [
            'gigs' => $this->gigService->getMyGigs(auth()->id()),
            'applications' => $this->gigService->getMyApplications(auth()->id()),
        ]);
    }
}
