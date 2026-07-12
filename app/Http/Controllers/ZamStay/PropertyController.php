<?php

namespace App\Http\Controllers\ZamStay;

use App\Http\Controllers\Controller;
use App\Models\ZamStay\ZamStayProperty;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $query = ZamStayProperty::active()->with(['owner', 'reviews']);

        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        if ($request->filled('min_price')) {
            $query->where('price_per_night', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_night', '<=', $request->max_price);
        }

        if ($request->filled('guests')) {
            $query->where('max_guests', '>=', $request->guests);
        }

        $properties = $query->paginate(12)->withQueryString();

        return Inertia::render('ZamStay/Search', [
            'properties' => $properties,
            'filters' => $request->only(['location', 'property_type', 'min_price', 'max_price', 'guests']),
        ]);
    }

    public function show(ZamStayProperty $property)
    {
        $property->load(['owner', 'reviews.user', 'bookings']);

        return Inertia::render('ZamStay/PropertyDetail', [
            'property' => $property,
        ]);
    }

    public function home(Request $request)
    {
        $featured = ZamStayProperty::active()->withCount('reviews')->inRandomOrder()->take(6)->get();
        $latest = ZamStayProperty::active()->latest()->take(8)->get();
        $locations = ZamStayProperty::active()->select('location')->distinct()->pluck('location');

        return Inertia::render('ZamStay/Home', [
            'featured' => $featured,
            'latest' => $latest,
            'locations' => $locations,
        ]);
    }

    public function search(Request $request)
    {
        return $this->index($request);
    }
}
