<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\VehicleModel;
use App\Infrastructure\Persistence\Eloquent\CMS\FuelRecordModel;
use App\Infrastructure\Persistence\Eloquent\CMS\VehicleMaintenanceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\TripLogModel;
use App\Infrastructure\Persistence\Eloquent\CMS\VehicleExpenseModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FleetController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = VehicleModel::where('company_id', $request->user()->current_company_id)
            ->with('currentDriver')
            ->paginate(20);

        return Inertia::render('CMS/Fleet/Index', ['vehicles' => $vehicles]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'registration_number' => 'required|string|max:50',
            'make' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'vehicle_type' => 'required|in:car,van,truck,pickup,motorcycle,other',
            'fuel_type' => 'required|in:petrol,diesel,electric,hybrid',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'current_mileage' => 'nullable|numeric|min:0',
        ]);

        $vehicle = VehicleModel::create([
            ...$validated,
            'company_id' => $request->user()->current_company_id,
            'status' => 'active',
        ]);

        return back()->with('success', 'Vehicle added successfully');
    }

    public function show(int $id)
    {
        $vehicle = VehicleModel::with(['fuelRecords', 'maintenanceRecords', 'tripLogs', 'expenses'])
            ->findOrFail($id);

        return Inertia::render('CMS/Fleet/Show', ['vehicle' => $vehicle]);
    }

    public function update(Request $request, int $id)
    {
        $vehicle = VehicleModel::findOrFail($id);
        $vehicle->update($request->validate([
            'current_mileage' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,maintenance,inactive,sold',
        ]));

        return back()->with('success', 'Vehicle updated');
    }

    public function recordFuel(Request $request, int $vehicleId)
    {
        $validated = $request->validate([
            'fuel_date' => 'required|date',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
            'mileage' => 'required|numeric|min:0',
            'filled_by' => 'nullable|string|max:255',
        ]);

        FuelRecordModel::create([
            ...$validated,
            'vehicle_id' => $vehicleId,
            'total_cost' => $validated['quantity'] * $validated['unit_price'],
        ]);

        return back()->with('success', 'Fuel record added');
    }

    public function maintenance(Request $request)
    {
        $maintenance = VehicleMaintenanceModel::whereHas('vehicle', fn($q) => 
            $q->where('company_id', $request->user()->current_company_id)
        )->with('vehicle')->latest()->paginate(20);

        return Inertia::render('CMS/Fleet/Maintenance', ['maintenance' => $maintenance]);
    }

    public function scheduleMaintenance(Request $request, int $vehicleId)
    {
        $validated = $request->validate([
            'maintenance_type' => 'required|in:service,repair,inspection,tire_change,oil_change,other',
            'scheduled_date' => 'required|date',
            'description' => 'required|string',
            'estimated_cost' => 'nullable|numeric|min:0',
        ]);

        VehicleMaintenanceModel::create([
            ...$validated,
            'vehicle_id' => $vehicleId,
            'status' => 'scheduled',
        ]);

        return back()->with('success', 'Maintenance scheduled');
    }

    public function completeMaintenance(Request $request, int $id)
    {
        $maintenance = VehicleMaintenanceModel::findOrFail($id);
        $maintenance->update([
            'status' => 'completed',
            'completed_date' => $request->completed_date,
            'actual_cost' => $request->actual_cost,
            'performed_by' => $request->performed_by,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Maintenance completed');
    }

    public function recordTrip(Request $request, int $vehicleId)
    {
        $validated = $request->validate([
            'trip_date' => 'required|date',
            'driver_id' => 'required|exists:users,id',
            'start_location' => 'required|string|max:255',
            'end_location' => 'required|string|max:255',
            'start_mileage' => 'required|numeric|min:0',
            'end_mileage' => 'required|numeric|min:0',
            'purpose' => 'nullable|string',
        ]);

        TripLogModel::create([
            ...$validated,
            'vehicle_id' => $vehicleId,
            'distance' => $validated['end_mileage'] - $validated['start_mileage'],
        ]);

        return back()->with('success', 'Trip logged');
    }

    public function recordExpense(Request $request, int $vehicleId)
    {
        $validated = $request->validate([
            'expense_date' => 'required|date',
            'expense_type' => 'required|in:fuel,maintenance,insurance,tax,parking,toll,fine,other',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        VehicleExpenseModel::create([
            ...$validated,
            'vehicle_id' => $vehicleId,
        ]);

        return back()->with('success', 'Expense recorded');
    }
}
