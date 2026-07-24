<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\TeamMemberRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamMemberController extends Controller
{
    public function __construct(
        private BusinessService $businessService,
        private TeamMemberRepositoryInterface $teamMemberRepo,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $members = $this->teamMemberRepo->findByBusiness($business->id);

        return Inertia::render('BizBoost/TeamMembers/Index', [
            'members' => $members,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|max:100',
            'bio' => 'nullable|string|max:500',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'boolean',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->teamMemberRepo->save(new \App\Domain\BizBoost\Entities\TeamMember(
            id: null,
            businessId: $business->id,
            name: $validated['name'],
            email: $validated['email'] ?? null,
            phone: $validated['phone'] ?? null,
            role: $validated['role'],
            bio: $validated['bio'] ?? null,
            photoPath: $request->hasFile('photo') ? $request->file('photo')->store('bizboost/team', 'public') : null,
            isActive: $validated['is_active'] ?? true,
            createdAt: null,
            updatedAt: null,
        ));

        return back()->with('success', 'Team member added successfully.');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|max:100',
            'bio' => 'nullable|string|max:500',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'boolean',
        ]);

        $existing = $this->teamMemberRepo->findById($id);
        if (!$existing) {
            abort(404);
        }

        $photoPath = $existing->photoPath;
        if ($request->hasFile('photo')) {
            if ($photoPath) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($photoPath);
            }
            $photoPath = $request->file('photo')->store('bizboost/team', 'public');
        }

        $this->teamMemberRepo->save(new \App\Domain\BizBoost\Entities\TeamMember(
            id: $existing->id,
            businessId: $existing->businessId,
            name: $validated['name'],
            email: $validated['email'] ?? null,
            phone: $validated['phone'] ?? null,
            role: $validated['role'],
            bio: $validated['bio'] ?? null,
            photoPath: $photoPath,
            isActive: $validated['is_active'] ?? true,
            createdAt: $existing->createdAt,
            updatedAt: null,
        ));

        return back()->with('success', 'Team member updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $this->teamMemberRepo->delete($id);
        return back()->with('success', 'Team member removed successfully.');
    }
}