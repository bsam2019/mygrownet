<?php

namespace App\Http\Middleware;

use App\Domain\Employee\Services\DelegationService;
use App\Models\Employee;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckDelegatedPermission
{
    public function __construct(
        protected DelegationService $delegationService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  The delegated permission key to check
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $this->unauthorized($request, 'Unauthenticated');
        }

        // Admins and superadmins always have access
        if ($user->hasRole('admin') || $user->hasRole('superadmin') || $user->hasRole('Administrator')) {
            return $next($request);
        }

        // Check if user has employee record
        $employee = Employee::where('user_id', $user->id)
            ->where('employment_status', 'active')
            ->first();

        if (!$employee) {
            return $this->unauthorized($request, 'Employee profile not found');
        }

        // Check if employee has the delegated permission
        if (!$this->delegationService->hasPermission($employee, $permission)) {
            return $this->unauthorized($request, 'You do not have permission to access this resource');
        }

        // Log the usage
        $this->delegationService->logUsage($employee, $permission, $user, [
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
            'url' => $request->url(),
        ]);

        // Store employee and permission in request for controllers
        $request->attributes->set('employee', $employee);
        $request->attributes->set('delegated_permission', $permission);

        return $next($request);
    }

    protected function unauthorized(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => $message], 403);
        }

        return redirect()->route('employee.portal.dashboard')
            ->with('error', $message);
    }
}
