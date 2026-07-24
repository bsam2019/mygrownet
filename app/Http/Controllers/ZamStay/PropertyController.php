<?php

namespace App\Http\Controllers\ZamStay;

use App\Domain\ZamStay\Services\PropertyService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PropertyController extends Controller
{
    public function __construct(
        private readonly PropertyService $propertyService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['location', 'property_type', 'min_price', 'max_price', 'guests']);

        $properties = $this->propertyService->search($filters);

        return Inertia::render('ZamStay/Search', [
            'properties' => $properties,
            'filters' => $filters,
        ]);
    }

    public function show(int $id)
    {
        $property = $this->propertyService->findOrFail($id);

        return Inertia::render('ZamStay/PropertyDetail', [
            'property' => $property->toArray(),
        ]);
    }

    public function home()
    {
        $data = $this->propertyService->getHomeData();

        return Inertia::render('ZamStay/Home', $data);
    }

    public function search(Request $request)
    {
        return $this->index($request);
    }
}
