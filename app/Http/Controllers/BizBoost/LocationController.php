<?php

namespace App\Http\Controllers\BizBoost;

use App\Domain\Module\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LocationController extends Controller
{
    private const MODULE_ID = 'bizboost';

    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request)
    {
        $business = $this->getBusiness($request);
        
        $locations = DB::table('bizboost_locations')
            ->where('business_id', $business->id)
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get();

        return Inertia::render('BizBoost/Locations/Index', [
            'locations' => $locations,
            'locationLimit' => $this->getLocationLimit($request),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('BizBoost/Locations/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'business_hours' => 'nullable|array',
        ]);

        $business = $this->getBusiness($request);

        // Check location limit
        $currentCount = DB::table('bizboost_locations')
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->count();

        $limit = $this->getLocationLimit($request);
        if ($currentCount >= $limit) {
            return back()->withErrors(['limit' => 'Location limit reached. Please upgrade your plan.']);
        }

        $isPrimary = $currentCount === 0;

        DB::table('bizboost_locations')->insert([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'phone' => $validated['phone'],
            'whatsapp' => $validated['whatsapp'],
            'business_hours' => json_encode($validated['business_hours'] ?? []),
            'is_primary' => $isPrimary,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // If this is the primary location, sync to business profile
        if ($isPrimary) {
            $this->syncPrimaryLocationToProfile($business);
        }

        return redirect()->route('bizboost.locations.index')
            ->with('success', 'Location added successfully.');
    }

    public function edit(Request $request, $id)
    {
        $business = $this->getBusiness($request);
        
        $location = DB::table('bizboost_locations')
            ->where('id', $id)
            ->where('business_id', $business->id)
            ->first();

        if (!$location) {
            abort(404);
        }

        $location->business_hours = json_decode($location->business_hours, true);

        return Inertia::render('BizBoost/Locations/Edit', [
            'location' => $location,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'business_hours' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $business = $this->getBusiness($request);

        // Check if this is the primary location
        $location = DB::table('bizboost_locations')
            ->where('id', $id)
            ->where('business_id', $business->id)
            ->first();

        DB::table('bizboost_locations')
            ->where('id', $id)
            ->where('business_id', $business->id)
            ->update([
                'name' => $validated['name'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'phone' => $validated['phone'],
                'whatsapp' => $validated['whatsapp'],
                'business_hours' => json_encode($validated['business_hours'] ?? []),
                'is_active' => $validated['is_active'] ?? true,
                'updated_at' => now(),
            ]);

        // If this is the primary location, sync to business profile
        if ($location && $location->is_primary) {
            $this->syncPrimaryLocationToProfile($business);
        }

        return redirect()->route('bizboost.locations.index')
            ->with('success', 'Location updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $business = $this->getBusiness($request);

        $location = DB::table('bizboost_locations')
            ->where('id', $id)
            ->where('business_id', $business->id)
            ->first();

        if ($location && !$location->is_primary) {
            DB::table('bizboost_locations')->where('id', $id)->delete();
            return back()->with('success', 'Location deleted.');
        }

        return back()->withErrors(['delete' => 'Cannot delete primary location.']);
    }

    public function setPrimary(Request $request, $id)
    {
        $business = $this->getBusiness($request);

        DB::transaction(function () use ($business, $id) {
            // Remove primary from all locations
            DB::table('bizboost_locations')
                ->where('business_id', $business->id)
                ->update(['is_primary' => false, 'updated_at' => now()]);

            // Set new primary
            DB::table('bizboost_locations')
                ->where('id', $id)
                ->where('business_id', $business->id)
                ->update(['is_primary' => true, 'updated_at' => now()]);

            // Sync primary location to business profile
            $this->syncPrimaryLocationToProfile($business);
        });

        return back()->with('success', 'Primary location updated.');
    }

    /**
     * Sync the primary location data to the business profile.
     * This keeps the business profile in sync with the primary location.
     */
    private function syncPrimaryLocationToProfile(BizBoostBusinessModel $business): void
    {
        $primaryLocation = DB::table('bizboost_locations')
            ->where('business_id', $business->id)
            ->where('is_primary', true)
            ->first();

        if ($primaryLocation) {
            $business->update([
                'address' => $primaryLocation->address,
                'city' => $primaryLocation->city,
                'phone' => $primaryLocation->phone ?? $business->phone,
                'whatsapp' => $primaryLocation->whatsapp ?? $business->whatsapp,
            ]);
        }
    }

    private function getBusiness(Request $request): BizBoostBusinessModel
    {
        return BizBoostBusinessModel::where('user_id', $request->user()->id)->firstOrFail();
    }

    private function getLocationLimit(Request $request): int
    {
        $user = $request->user();
        $limits = $this->subscriptionService->getUserLimits($user, self::MODULE_ID);
        return $limits['locations'] ?? 1;
    }
}
