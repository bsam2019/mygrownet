<?php

namespace App\Http\Controllers\Api\Platform;

use App\Domain\Core\Models\Organization;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $organizations = Organization::where('owner_id', $request->user()->id)
            ->orWhereHas('members', fn($q) => $q->where('user_id', $request->user()->id))
            ->get();

        return response()->json(['data' => $organizations]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:organizations,slug',
            'type' => 'sometimes|string|in:company,non_profit,individual',
        ]);

        $organization = Organization::create([
            ...$validated,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'owner_id' => $request->user()->id,
        ]);

        return response()->json(['data' => $organization], 201);
    }

    public function show(Organization $organization): JsonResponse
    {
        $this->authorize('view', $organization);

        return response()->json(['data' => $organization]);
    }

    public function update(Request $request, Organization $organization): JsonResponse
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:organizations,slug,' . $organization->id,
            'type' => 'sometimes|string|in:company,non_profit,individual',
            'status' => 'sometimes|string|in:active,suspended,archived',
        ]);

        $organization->update($validated);

        return response()->json(['data' => $organization]);
    }

    public function destroy(Organization $organization): JsonResponse
    {
        $this->authorize('delete', $organization);

        $organization->delete();

        return response()->json(null, 204);
    }
}
