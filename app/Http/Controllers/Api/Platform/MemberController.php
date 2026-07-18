<?php

namespace App\Http\Controllers\Api\Platform;

use App\Domain\Core\Models\Organization;
use App\Domain\Core\Models\OrganizationMember;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Organization $organization): JsonResponse
    {
        $this->authorize('view', $organization);

        $members = $organization->members()->with('user:id,name,email')->get();

        return response()->json(['data' => $members]);
    }

    public function store(Request $request, Organization $organization): JsonResponse
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'sometimes|string|max:50',
        ]);

        $existing = OrganizationMember::where('organization_id', $organization->id)
            ->where('user_id', $validated['user_id'])
            ->exists();

        if ($existing) {
            return response()->json([
                'error' => ['code' => 'CONFLICT', 'message' => 'User is already a member.'],
            ], 409);
        }

        $member = $organization->members()->create($validated);

        return response()->json(['data' => $member], 201);
    }

    public function destroy(Organization $organization, User $user): JsonResponse
    {
        $this->authorize('update', $organization);

        $deleted = OrganizationMember::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Member not found.'],
            ], 404);
        }

        return response()->json(null, 204);
    }
}
