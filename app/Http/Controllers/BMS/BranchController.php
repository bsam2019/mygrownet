<?php

namespace App\Http\Controllers\BMS;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BMS\BranchModel;
use App\Infrastructure\Persistence\Eloquent\BMS\CmsUserModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $branches = BranchModel::where('company_id', $companyId)
            ->with(['manager.user', 'departments'])
            ->withCount('departments')
            ->when($request->search, fn($q, $s) => $q->where(function($q) use ($s) {
                $q->where('branch_name', 'like', "%{$s}%")
                  ->orWhere('branch_code', 'like', "%{$s}%")
                  ->orWhere('city', 'like', "%{$s}%")
                  ->orWhere('province', 'like', "%{$s}%");
            }))
            ->when($request->status, fn($q, $s) => $s === 'active' ? $q->where('is_active', true) : $q->where('is_active', false))
            ->orderBy('is_head_office', 'desc')
            ->orderBy('branch_name')
            ->paginate(20);

        return Inertia::render('BMS/Branches/Index', [
            'branches' => $branches,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $managers = CmsUserModel::where('company_id', $companyId)
            ->with('user')
            ->get()
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->user?->name ?? "User #{$u->id}"]);

        return Inertia::render('BMS/Branches/Create', [
            'managers' => $managers,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $validated = $request->validate([
            'branch_code' => 'required|string|max:50|unique:cms_branches,branch_code,NULL,id,company_id,' . $companyId,
            'branch_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'is_head_office' => 'boolean',
            'is_active' => 'boolean',
            'manager_id' => 'nullable|exists:cms_users,id',
        ]);

        $validated['company_id'] = $companyId;

        BranchModel::create($validated);

        return redirect()
            ->route('bms.branches.index')
            ->with('success', 'Branch created successfully');
    }

    public function edit(BranchModel $branch)
    {
        $companyId = request()->user()->cmsUser->company_id;
        if ($branch->company_id !== $companyId) abort(403);

        $branch->load('manager.user');

        $managers = CmsUserModel::where('company_id', $companyId)
            ->with('user')
            ->get()
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->user?->name ?? "User #{$u->id}"]);

        return Inertia::render('BMS/Branches/Edit', [
            'branch' => [
                'id' => $branch->id,
                'branch_code' => $branch->branch_code,
                'branch_name' => $branch->branch_name,
                'phone' => $branch->phone,
                'email' => $branch->email,
                'address' => $branch->address,
                'city' => $branch->city,
                'province' => $branch->province,
                'is_head_office' => $branch->is_head_office,
                'is_active' => $branch->is_active,
                'manager_id' => $branch->manager_id,
            ],
            'managers' => $managers,
        ]);
    }

    public function update(Request $request, BranchModel $branch)
    {
        $companyId = $request->user()->cmsUser->company_id;
        if ($branch->company_id !== $companyId) abort(403);

        $validated = $request->validate([
            'branch_code' => 'required|string|max:50|unique:cms_branches,branch_code,' . $branch->id . ',id,company_id,' . $companyId,
            'branch_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'is_head_office' => 'boolean',
            'is_active' => 'boolean',
            'manager_id' => 'nullable|exists:cms_users,id',
        ]);

        // Prevent unsetting the only head office
        if (!$validated['is_head_office'] && $branch->is_head_office) {
            $otherHeadOffice = BranchModel::where('company_id', $companyId)
                ->where('is_head_office', true)
                ->where('id', '!=', $branch->id)
                ->exists();
            if (!$otherHeadOffice) {
                return back()->with('error', 'Cannot unset the only head office branch. Set another branch as head office first.');
            }
        }

        $branch->update($validated);

        return redirect()
            ->route('bms.branches.index')
            ->with('success', 'Branch updated successfully');
    }

    public function destroy(BranchModel $branch)
    {
        $companyId = request()->user()->cmsUser->company_id;
        if ($branch->company_id !== $companyId) abort(403);

        if ($branch->departments()->count() > 0) {
            return back()->with('error', 'Cannot delete branch with assigned departments');
        }

        if ($branch->is_head_office) {
            return back()->with('error', 'Cannot delete the head office branch');
        }

        $branch->delete();

        return redirect()
            ->route('bms.branches.index')
            ->with('success', 'Branch deleted successfully');
    }
}
