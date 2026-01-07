<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GeopamuAdminManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin'); // Only MyGrowNet admins can access
    }

    public function index(): Response
    {
        $geopamuAdmins = User::permission('manage-geopamu')
            ->select('id', 'name', 'email', 'created_at')
            ->get();

        $allUsers = User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/GeopamuAdminManagement', [
            'geopamuAdmins' => $geopamuAdmins,
            'allUsers' => $allUsers
        ]);
    }

    public function assign(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->user_id);
        
        if ($user->can('manage-geopamu')) {
            return back()->with('error', 'User already has Geopamu admin access.');
        }

        $user->givePermissionTo('manage-geopamu');

        return back()->with('success', "Geopamu admin access granted to {$user->name}.");
    }

    public function revoke(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->revokePermissionTo('manage-geopamu');

        return back()->with('success', "Geopamu admin access revoked from {$user->name}.");
    }
}
