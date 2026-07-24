<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\LocationRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends Controller
{
    public function __construct(
        private BusinessService $businessService,
        private LocationRepositoryInterface $locationRepo,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $locations = $this->locationRepo->findByBusiness($business->id);

        return Inertia::render('BizBoost/Locations/Index', [
            'locations' => $locations,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'business_hours' => 'nullable|array',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->locationRepo->save(new \App\Domain\BizBoost\Entities\Location(
            id: null,
            businessId: $business->id,
            name: $validated['name'],
            address: $validated['address'] ?? null,
            city: $validated['city'] ?? null,
            province: $validated['province'] ?? null,
            postalCode: $validated['postal_code'] ?? null,
            country: $validated['country'] ?? null,
            phone: $validated['phone'] ?? null,
            email: $validated['email'] ?? null,
            whatsapp: $validated['whatsapp'] ?? null,
            latitude: $validated['latitude'] ?? null,
            longitude: $validated['longitude'] ?? null,
            isPrimary: $validated['is_primary'] ?? false,
            isActive: $validated['is_active'] ?? true,
            businessHours: $validated['business_hours'] ?? null,
            createdAt: null,
            updatedAt: null,
        ));

        return back()->with('success', 'Location added successfully.');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'business_hours' => 'nullable|array',
        ]);

        $existing = $this->locationRepo->findById($id);
        if (!$existing) {
            abort(404);
        }

        $this->locationRepo->save(new \App\Domain\BizBoost\Entities\Location(
            id: $existing->id,
            businessId: $existing->businessId,
            name: $validated['name'],
            address: $validated['address'] ?? null,
            city: $validated['city'] ?? null,
            province: $validated['province'] ?? null,
            postalCode: $validated['postal_code'] ?? null,
            country: $validated['country'] ?? null,
            phone: $validated['phone'] ?? null,
            email: $validated['email'] ?? null,
            whatsapp: $validated['whatsapp'] ?? null,
            latitude: $validated['latitude'] ?? null,
            longitude: $validated['longitude'] ?? null,
            isPrimary: $validated['is_primary'] ?? $existing->isPrimary,
            isActive: $validated['is_active'] ?? $existing->isActive,
            businessHours: $validated['business_hours'] ?? $existing->businessHours,
            createdAt: $existing->createdAt,
            updatedAt: null,
        ));

        return back()->with('success', 'Location updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $this->locationRepo->delete($id);
        return back()->with('success', 'Location deleted successfully.');
    }
}