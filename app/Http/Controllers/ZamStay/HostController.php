<?php

namespace App\Http\Controllers\ZamStay;

use App\Domain\ZamStay\Services\BookingService;
use App\Domain\ZamStay\Services\PropertyService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class HostController extends Controller
{
    public function __construct(
        private readonly PropertyService $propertyService,
        private readonly BookingService $bookingService,
    ) {}

    public function uploadImage(Request $request)
    {
        $request->validate(['image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120']);

        $path = $request->file('image')->store('zamstay/properties', 'public');

        return response()->json([
            'url' => Storage::url($path),
            'path' => $path,
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $stats = $this->bookingService->getHostStats($user->id);
        $properties = $this->propertyService->findByOwner($user->id);
        $bookings = $this->bookingService->getHostBookings($user->id);

        return Inertia::render('ZamStay/Host/Dashboard', [
            'stats' => $stats,
            'recentBookings' => array_slice($bookings, 0, 10),
            'properties' => $properties,
        ]);
    }

    public function properties(Request $request)
    {
        $properties = $this->propertyService->findByOwner($request->user()->id);

        return Inertia::render('ZamStay/Host/Properties', [
            'properties' => $properties,
        ]);
    }

    public function createProperty()
    {
        return Inertia::render('ZamStay/Host/PropertyForm', [
            'property' => null,
        ]);
    }

    public function storeProperty(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'price_per_night' => 'required|numeric|min:0',
            'property_type' => 'required|in:hotel,lodge,guest_house,home_stay',
            'max_guests' => 'required|integer|min:1',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'amenities' => 'nullable|array',
            'images' => 'nullable|array',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $this->propertyService->create($request->user()->id, $validated);

        return redirect()->route('zamstay.host.properties')
            ->with('success', 'Property created successfully.');
    }

    public function editProperty(int $id, Request $request)
    {
        $property = $this->propertyService->findOrFail($id);

        if ($property->ownerId !== $request->user()->id) {
            abort(403);
        }

        return Inertia::render('ZamStay/Host/PropertyForm', [
            'property' => $property->toArray(),
        ]);
    }

    public function updateProperty(int $id, Request $request)
    {
        $property = $this->propertyService->findOrFail($id);

        if ($property->ownerId !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'price_per_night' => 'required|numeric|min:0',
            'property_type' => 'required|in:hotel,lodge,guest_house,home_stay',
            'max_guests' => 'required|integer|min:1',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'amenities' => 'nullable|array',
            'images' => 'nullable|array',
            'is_active' => 'boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $this->propertyService->update($property, $validated);

        return redirect()->route('zamstay.host.properties')
            ->with('success', 'Property updated successfully.');
    }

    public function bookings(Request $request)
    {
        $bookings = $this->bookingService->getHostBookings($request->user()->id);

        return Inertia::render('ZamStay/Host/Bookings', [
            'bookings' => $bookings,
        ]);
    }
}
