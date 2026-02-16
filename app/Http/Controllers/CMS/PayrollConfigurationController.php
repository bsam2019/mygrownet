<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Infrastructure\Persistence\Eloquent\CMS\AllowanceTypeModel;
use App\Infrastructure\Persistence\Eloquent\CMS\DeductionTypeModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerAllowanceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerDeductionModel;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel;

class PayrollConfigurationController extends Controller
{
    // Allowance Types
    public function allowanceTypesIndex(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $allowanceTypes = AllowanceTypeModel::where('company_id', $companyId)
            ->orderBy('allowance_name')
            ->get();

        return Inertia::render('CMS/Payroll/AllowanceTypes', [
            'allowanceTypes' => $allowanceTypes,
        ]);
    }

    public function storeAllowanceType(Request $request)
    {
        $validated = $request->validate([
            'allowance_name' => 'required|string|max:100',
            'allowance_code' => 'required|string|max:50|unique:cms_allowance_types',
            'calculation_type' => 'required|in:fixed,percentage_of_basic,custom',
            'default_amount' => 'nullable|numeric|min:0',
            'is_taxable' => 'boolean',
            'is_pensionable' => 'boolean',
        ]);

        $allowanceType = AllowanceTypeModel::create(array_merge($validated, [
            'company_id' => $request->user()->company_id,
            'is_active' => true,
        ]));

        return redirect()->back()->with('success', 'Allowance type created successfully');
    }

    public function updateAllowanceType(Request $request, int $id)
    {
        $allowanceType = AllowanceTypeModel::where('company_id', $request->user()->company_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'allowance_name' => 'required|string|max:100',
            'calculation_type' => 'required|in:fixed,percentage_of_basic,custom',
            'default_amount' => 'nullable|numeric|min:0',
            'is_taxable' => 'boolean',
            'is_pensionable' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $allowanceType->update($validated);

        return redirect()->back()->with('success', 'Allowance type updated successfully');
    }

    // Deduction Types
    public function deductionTypesIndex(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $deductionTypes = DeductionTypeModel::where('company_id', $companyId)
            ->orderBy('deduction_name')
            ->get();

        return Inertia::render('CMS/Payroll/DeductionTypes', [
            'deductionTypes' => $deductionTypes,
        ]);
    }

    public function storeDeductionType(Request $request)
    {
        $validated = $request->validate([
            'deduction_name' => 'required|string|max:100',
            'deduction_code' => 'required|string|max:50|unique:cms_deduction_types',
            'calculation_type' => 'required|in:fixed,percentage_of_gross,percentage_of_basic,custom',
            'default_amount' => 'nullable|numeric|min:0',
            'default_percentage' => 'nullable|numeric|min:0|max:100',
            'is_statutory' => 'boolean',
        ]);

        $deductionType = DeductionTypeModel::create(array_merge($validated, [
            'company_id' => $request->user()->company_id,
            'is_active' => true,
        ]));

        return redirect()->back()->with('success', 'Deduction type created successfully');
    }

    public function updateDeductionType(Request $request, int $id)
    {
        $deductionType = DeductionTypeModel::where('company_id', $request->user()->company_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'deduction_name' => 'required|string|max:100',
            'calculation_type' => 'required|in:fixed,percentage_of_gross,percentage_of_basic,custom',
            'default_amount' => 'nullable|numeric|min:0',
            'default_percentage' => 'nullable|numeric|min:0|max:100',
            'is_statutory' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $deductionType->update($validated);

        return redirect()->back()->with('success', 'Deduction type updated successfully');
    }

    // Worker Allowances
    public function workerAllowancesIndex(Request $request, int $workerId)
    {
        $worker = WorkerModel::where('company_id', $request->user()->company_id)
            ->with(['allowances.allowanceType'])
            ->findOrFail($workerId);

        $availableAllowanceTypes = AllowanceTypeModel::where('company_id', $request->user()->company_id)
            ->where('is_active', true)
            ->get();

        return Inertia::render('CMS/Workers/Allowances', [
            'worker' => $worker,
            'allowances' => $worker->allowances,
            'availableAllowanceTypes' => $availableAllowanceTypes,
        ]);
    }

    public function storeWorkerAllowance(Request $request, int $workerId)
    {
        $worker = WorkerModel::where('company_id', $request->user()->company_id)
            ->findOrFail($workerId);

        $validated = $request->validate([
            'allowance_type_id' => 'required|exists:cms_allowance_types,id',
            'amount' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
        ]);

        WorkerAllowanceModel::create(array_merge($validated, [
            'worker_id' => $workerId,
            'is_active' => true,
        ]));

        return redirect()->back()->with('success', 'Allowance added successfully');
    }

    public function updateWorkerAllowance(Request $request, int $workerId, int $id)
    {
        $allowance = WorkerAllowanceModel::where('worker_id', $workerId)
            ->findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'is_active' => 'boolean',
        ]);

        $allowance->update($validated);

        return redirect()->back()->with('success', 'Allowance updated successfully');
    }

    public function deleteWorkerAllowance(int $workerId, int $id)
    {
        $allowance = WorkerAllowanceModel::where('worker_id', $workerId)
            ->findOrFail($id);

        $allowance->delete();

        return redirect()->back()->with('success', 'Allowance removed successfully');
    }

    // Worker Deductions
    public function workerDeductionsIndex(Request $request, int $workerId)
    {
        $worker = WorkerModel::where('company_id', $request->user()->company_id)
            ->with(['deductions.deductionType'])
            ->findOrFail($workerId);

        $availableDeductionTypes = DeductionTypeModel::where('company_id', $request->user()->company_id)
            ->where('is_active', true)
            ->where('is_statutory', false) // Only non-statutory deductions can be manually added
            ->get();

        return Inertia::render('CMS/Workers/Deductions', [
            'worker' => $worker,
            'deductions' => $worker->deductions,
            'availableDeductionTypes' => $availableDeductionTypes,
        ]);
    }

    public function storeWorkerDeduction(Request $request, int $workerId)
    {
        $worker = WorkerModel::where('company_id', $request->user()->company_id)
            ->findOrFail($workerId);

        $validated = $request->validate([
            'deduction_type_id' => 'required|exists:cms_deduction_types,id',
            'amount' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
        ]);

        WorkerDeductionModel::create(array_merge($validated, [
            'worker_id' => $workerId,
            'is_active' => true,
        ]));

        return redirect()->back()->with('success', 'Deduction added successfully');
    }

    public function updateWorkerDeduction(Request $request, int $workerId, int $id)
    {
        $deduction = WorkerDeductionModel::where('worker_id', $workerId)
            ->findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'is_active' => 'boolean',
        ]);

        $deduction->update($validated);

        return redirect()->back()->with('success', 'Deduction updated successfully');
    }

    public function deleteWorkerDeduction(int $workerId, int $id)
    {
        $deduction = WorkerDeductionModel::where('worker_id', $workerId)
            ->findOrFail($id);

        $deduction->delete();

        return redirect()->back()->with('success', 'Deduction removed successfully');
    }
}
