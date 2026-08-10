<?php

namespace App\Domain\GrowNet\Presentation\Http\Controllers\Web;

use App\Domain\GrowNet\Services\ResourceEntitlementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LibraryResourceController extends Controller
{
    public function __construct(
        private ResourceEntitlementService $entitlementService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $resources = $this->entitlementService->getEntitledResources($user);

        return Inertia::render('GrowNet/Library', [
            'resources' => $resources,
            'userLevel' => (int) ($user->current_professional_level_id ?? 1),
        ]);
    }
}
