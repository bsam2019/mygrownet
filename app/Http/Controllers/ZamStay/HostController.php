<?php

namespace App\Http\Controllers\ZamStay;

use App\Http\Controllers\Controller;
use App\Models\ZamStayProperty;
use App\Models\ZamStayBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class HostController extends Controller
{
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
        $properties = ZamStayProperty::where('owner_id', $user->id)->get();
        $propertyIds = $properties->pluck('id');

        $stats = [
            'total_properties' => $properties->count(),
            'active_properties' => $properties->where('is_active', true)->count(),
            'total_bookings' => ZamStayBooking::whereIn('property_id', $propertyIds)->count(),
            'pending_bookings' => ZamStayBooking::whereIn('property_id', $propertyIds)->where('status', 'pending')->count(),
            'confirmed_bookings' => ZamStayBooking::whereIn('property_id', $propertyIds)->where('status', 'confirmed')->count(),
            'total_revenue' => ZamStayBooking::whereIn('property_id', $propertyIds)->where('status', 'confirmed')->sum('total_price'),
        ];

        $recentBookings = ZamStayBooking::whereIn('property_id', $propertyIds)
            ->with(['property', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return Inertia::render('ZamStay/Host/Dashboard', [
            'stats' => $stats,
            'recentBookings' => $recentBookings,
            'properties' => $properties,
        ]);
    }

    public function properties(Request $request)
    {
        $properties = ZamStayProperty::where('owner_id', $request->user()->id)
            ->withCount('bookings')
            ->withCount('reviews')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

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

        $validated['owner_id'] = $request->user()->id;

        ZamStayProperty::create($validated);

        return redirect()->route('zamstay.host.properties')
            ->with('success', 'Property created successfully.');
    }

    public function editProperty(ZamStayProperty $property, Request $request)
    {
        if ($property->owner_id !== $request->user()->id) {
            abort(403);
        }

        return Inertia::render('ZamStay/Host/PropertyForm', [
            'property' => $property,
        ]);
    }

    public function updateProperty(ZamStayProperty $property, Request $request)
    {
        if ($property->owner_id !== $request->user()->id) {
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

        $property->update($validated);

        return redirect()->route('zamstay.host.properties')
            ->with('success', 'Property updated successfully.');
    }

    public function bookings(Request $request)
    {
        $propertyIds = ZamStayProperty::where('owner_id', $request->user()->id)->pluck('id');

        $bookings = ZamStayBooking::whereIn('property_id', $propertyIds)
            ->with(['property', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('ZamStay/Host/Bookings', [
            'bookings' => $bookings,
        ]);
    }
}
