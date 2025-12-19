<?php

namespace App\Http\Controllers\LifePlus;

use App\Http\Controllers\Controller;
use App\Domain\LifePlus\Services\ProfileService;
use App\Domain\Module\Services\TierConfigurationService;
use App\Domain\Wallet\Services\UnifiedWalletService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profileService,
        protected TierConfigurationService $tierConfigService,
        protected UnifiedWalletService $walletService
    ) {}

    public function index()
    {
        $userId = auth()->id();

        return Inertia::render('LifePlus/Profile/Index', [
            'profile' => $this->profileService->getProfile($userId),
            'stats' => $this->profileService->getStats($userId),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'location' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:500',
            'avatar_url' => 'nullable|url',
        ]);

        $profile = $this->profileService->updateProfile(auth()->id(), $validated);

        if ($request->wantsJson()) {
            return response()->json($profile);
        }

        return back()->with('success', 'Profile updated');
    }

    public function skills()
    {
        return Inertia::render('LifePlus/Profile/Skills', [
            'profile' => $this->profileService->getProfile(auth()->id()),
        ]);
    }

    public function updateSkills(Request $request)
    {
        $validated = $request->validate([
            'skills' => 'required|array',
            'skills.*' => 'string|max:50',
        ]);

        $profile = $this->profileService->updateSkills(auth()->id(), $validated['skills']);

        if ($request->wantsJson()) {
            return response()->json($profile);
        }

        return back()->with('success', 'Skills updated');
    }

    public function stats()
    {
        return response()->json(
            $this->profileService->getStats(auth()->id())
        );
    }

    public function settings()
    {
        return Inertia::render('LifePlus/Profile/Settings', [
            'profile' => $this->profileService->getProfile(auth()->id()),
        ]);
    }

    public function subscription()
    {
        $user = auth()->user();
        
        // Get dynamic tiers from admin-configured database or config fallback
        $tiers = $this->tierConfigService->getAllTiersForDisplay('lifeplus');
        
        // Access info is already injected by InjectLifePlusAccess middleware
        return Inertia::render('LifePlus/Profile/Subscription', [
            'walletBalance' => $this->walletService->calculateBalance($user),
            'tiers' => $tiers,
        ]);
    }
}
