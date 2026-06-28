<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\SafetyIncidentModel;
use App\Infrastructure\Persistence\Eloquent\CMS\SafetyInspectionModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PPEItemModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PPEDistributionModel;
use App\Infrastructure\Persistence\Eloquent\CMS\SafetyTrainingModel;
use App\Infrastructure\Persistence\Eloquent\CMS\TrainingRecordModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SafetyController extends Controller
{
    public function incidents(Request $request)
    {
        $incidents = SafetyIncidentModel::where('company_id', $request->user()->current_company_id)
            ->with(['reportedBy', 'investigatedBy'])
            ->latest('incident_date')
            ->paginate(20);

        return Inertia::render('CMS/Safety/Incidents/Index', ['incidents' => $incidents]);
    }

    public function storeIncident(Request $request)
    {
        $validated = $request->validate([
            'incident_number' => 'required|string|max:50',
            'incident_date' => 'required|date',
            'incident_time' => 'nullable|date_format:H:i',
            'location' => 'required|string|max:255',
            'incident_type' => 'required|in:injury,near_miss,property_damage,environmental,other',
            'severity' => 'required|in:minor,moderate,major,critical,fatal',
            'description' => 'required|string',
            'immediate_action' => 'nullable|string',
            'reported_by' => 'required|exists:users,id',
        ]);

        $incident = SafetyIncidentModel::create([
            ...$validated,
            'company_id' => $request->user()->current_company_id,
            'status' => 'reported',
        ]);

        if ($request->has('involved_persons')) {
            foreach ($request->involved_persons as $person) {
                $incident->involvedPersons()->create($person);
            }
        }

        return back()->with('success', 'Incident reported');
    }

    public function showIncident(int $id)
    {
        $incident = SafetyIncidentModel::with(['involvedPersons', 'reportedBy', 'investigatedBy'])
            ->findOrFail($id);

        return Inertia::render('CMS/Safety/Incidents/Show', ['incident' => $incident]);
    }

    public function inspections(Request $request)
    {
        $inspections = SafetyInspectionModel::where('company_id', $request->user()->current_company_id)
            ->with('inspectedBy')
            ->latest('inspection_date')
            ->paginate(20);

        return Inertia::render('CMS/Safety/Inspections/Index', ['inspections' => $inspections]);
    }

    public function storeInspection(Request $request)
    {
        $validated = $request->validate([
            'inspection_date' => 'required|date',
            'inspection_type' => 'required|in:site,equipment,vehicle,workplace,other',
            'location' => 'required|string|max:255',
            'inspected_by' => 'required|exists:users,id',
            'findings' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'status' => 'required|in:passed,failed,conditional',
        ]);

        SafetyInspectionModel::create([
            ...$validated,
            'company_id' => $request->user()->current_company_id,
        ]);

        return back()->with('success', 'Inspection recorded');
    }

    public function ppe(Request $request)
    {
        $ppe = PPEItemModel::where('company_id', $request->user()->current_company_id)
            ->withCount('distributions')
            ->paginate(20);

        return Inertia::render('CMS/Safety/PPE/Index', ['ppe' => $ppe]);
    }

    public function storePPE(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'item_type' => 'required|in:helmet,gloves,boots,vest,goggles,mask,harness,other',
            'quantity_in_stock' => 'required|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
        ]);

        PPEItemModel::create([
            ...$validated,
            'company_id' => $request->user()->current_company_id,
        ]);

        return back()->with('success', 'PPE item added');
    }

    public function distributePPE(Request $request, int $id)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'quantity' => 'required|integer|min:1',
            'distribution_date' => 'required|date',
            'condition' => 'required|in:new,good,fair,poor',
            'notes' => 'nullable|string',
        ]);

        $ppe = PPEItemModel::findOrFail($id);
        
        if ($ppe->quantity_in_stock < $validated['quantity']) {
            return back()->withErrors(['quantity' => 'Insufficient stock']);
        }

        $ppe->distributions()->create([
            ...$validated,
            'distributed_by' => $request->user()->id,
        ]);

        $ppe->decrement('quantity_in_stock', $validated['quantity']);

        return back()->with('success', 'PPE distributed');
    }

    public function training(Request $request)
    {
        $training = SafetyTrainingModel::where('company_id', $request->user()->current_company_id)
            ->withCount('records')
            ->latest()
            ->paginate(20);

        return Inertia::render('CMS/Safety/Training/Index', ['training' => $training]);
    }

    public function storeTraining(Request $request)
    {
        $validated = $request->validate([
            'training_name' => 'required|string|max:255',
            'training_type' => 'required|in:induction,refresher,specialized,certification,other',
            'description' => 'nullable|string',
            'duration_hours' => 'nullable|numeric|min:0',
            'validity_period_months' => 'nullable|integer|min:1',
            'is_mandatory' => 'boolean',
        ]);

        SafetyTrainingModel::create([
            ...$validated,
            'company_id' => $request->user()->current_company_id,
        ]);

        return back()->with('success', 'Training program created');
    }

    public function recordTraining(Request $request, int $id)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'training_date' => 'required|date',
            'trainer_name' => 'nullable|string|max:255',
            'score' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:completed,failed,in_progress',
            'certificate_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $training = SafetyTrainingModel::findOrFail($id);
        
        $expiryDate = null;
        if ($training->validity_period_months && $validated['status'] === 'completed') {
            $expiryDate = now()->addMonths($training->validity_period_months);
        }

        $training->records()->create([
            ...$validated,
            'expiry_date' => $expiryDate,
        ]);

        return back()->with('success', 'Training record added');
    }
}
