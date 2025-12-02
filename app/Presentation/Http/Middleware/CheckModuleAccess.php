<?php

namespace App\Presentation\Http\Middleware;

use App\Application\UseCases\Module\CheckModuleAccessUseCase;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    public function __construct(
        private CheckModuleAccessUseCase $checkAccessUseCase
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $moduleId): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $accessDTO = $this->checkAccessUseCase->execute($user, $moduleId);

        if (!$accessDTO->hasAccess) {
            return redirect()
                ->route('home-hub.index')
                ->with('error', $accessDTO->reason ?? 'You do not have access to this module.');
        }

        // Share access info with the view
        $request->attributes->set('moduleAccess', $accessDTO);

        return $next($request);
    }
}
