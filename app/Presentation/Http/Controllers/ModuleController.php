<?php

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\Module\GetModuleByIdUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModuleController extends Controller
{
    public function __construct(
        private GetModuleByIdUseCase $getModuleByIdUseCase
    ) {}

    /**
     * Display a specific module
     */
    public function show(Request $request, string $moduleId): Response
    {
        $moduleDTO = $this->getModuleByIdUseCase->execute($moduleId);

        if (!$moduleDTO) {
            abort(404, 'Module not found');
        }

        // Get module access info from middleware
        $moduleAccess = $request->attributes->get('moduleAccess');

        return Inertia::render('Module/Show', [
            'module' => $moduleDTO->toArray(),
            'access' => $moduleAccess?->toArray(),
        ]);
    }
}
