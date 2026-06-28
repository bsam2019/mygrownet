<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsEmployee
{
    /**
     * Handle an incoming request.
     *
     * Ensures the authenticated user has an associated employee record
     * AND has the 'employee' role assigned.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user has the employee role (admins can also access employee portal)
        if (!$user->hasRole('employee') && !$user->hasRole('admin') && !$user->hasRole('superadmin')) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You do not have the employee role.'], 403);
            }
            // Redirect non-employees to their appropriate dashboard
            if ($user->hasRole('Administrator') || $user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('mygrownet.classic-dashboard')
                ->with('error', 'You do not have access to the employee portal.');
        }

        // Check if user has an employee record
        $employee = Employee::where('user_id', $user->id)
            ->where('employment_status', 'active')
            ->first();

        // Admins without employee record can still access (for support purposes)
        if (!$employee && !$user->hasRole('admin') && !$user->hasRole('superadmin')) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Employee profile not set up.'], 403);
            }
            // User has role but no employee record - show error page
            return redirect()->route('mygrownet.classic-dashboard')
                ->with('error', 'Your employee profile is not set up. Please contact HR to complete your onboarding.');
        }

        // Store employee in request for easy access
        if ($employee) {
            $request->attributes->set('employee', $employee);
        }

        return $next($request);
    }
}
