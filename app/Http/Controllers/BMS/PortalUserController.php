<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerModel;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalUserController extends Controller
{
    public function index(Request $request): Response
    {
        $companyId = $request->attributes->get('company_id') ?? session('current_company_id');

        $users = User::whereHas('portalCustomers', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->with(['portalCustomers' => function ($q) use ($companyId) {
            $q->wherePivot('company_id', $companyId);
        }])->orderBy('name')->paginate(20);

        $allUsers = User::orderBy('name')->get(['id', 'name', 'email']);

        $customers = CustomerModel::forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        return Inertia::render('CMS/Portal/Users', [
            'users' => $users,
            'allUsers' => $allUsers,
            'customers' => $customers,
        ]);
    }

    public function link(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'customer_id' => 'required|exists:cms_customers,id',
        ]);

        $companyId = $request->attributes->get('company_id') ?? session('current_company_id');

        $user = User::findOrFail($validated['user_id']);
        $customer = CustomerModel::findOrFail($validated['customer_id']);

        if ($user->portalCustomers()->where('customer_id', $customer->id)->exists()) {
            return back()->with('warning', 'User already linked to this customer.');
        }

        $user->portalCustomers()->attach($customer->id, [
            'company_id' => $companyId,
            'is_active' => true,
        ]);

        return back()->with('success', 'User linked to customer successfully.');
    }

    public function unlink(Request $request, int $userId, int $customerId): RedirectResponse
    {
        $user = User::findOrFail($userId);

        $user->portalCustomers()->detach($customerId);

        return back()->with('success', 'User unlinked from customer.');
    }
}
