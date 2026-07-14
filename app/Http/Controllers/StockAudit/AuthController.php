<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Repositories\CompanyRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        $companyId = $request->attributes->get('stock_audit_company_id');
        $company = $companyId
            ? app(CompanyRepositoryInterface::class)->findById(CompanyId::fromInt($companyId))
            : null;

        return Inertia::render('StockAudit/Login', [
            'company' => $company ? $company->toArray() : null,
        ]);
    }

    public function login(Request $request)
    {
        \Log::debug('StockFlow AuthController::login called', [
            'email' => $request->email,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'host' => $request->getHost(),
            'session_has_company' => $request->session()->has('stock_audit_company_id'),
            'session_prev_url' => $request->session()->previousUrl(),
            'has_token' => $request->has('_token'),
            'has_xsrf_header' => $request->hasHeader('X-XSRF-TOKEN'),
            'has_csrf_header' => $request->hasHeader('X-CSRF-TOKEN'),
        ]);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $attempted = Auth::guard('stockflow')->attempt($credentials, $request->boolean('remember'));

        \Log::debug('StockFlow AuthController::login attempt result', [
            'success' => $attempted,
            'email' => $credentials['email'],
        ]);

        if ($attempted) {
            $request->session()->regenerate();
            $account = $this->getAccountFromRequest($request);

            $routeUrl = route('stockflow.sub.dashboard', ['account' => $account], false);

            \Log::debug('StockFlow AuthController::login success - redirecting', [
                'account' => $account,
                'route_url' => $routeUrl,
            ]);

            return redirect()->intended($routeUrl);
        }

        \Log::debug('StockFlow AuthController::login failed', [
            'back_url' => url()->previous(),
        ]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('stockflow')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function getAccountFromRequest(Request $request): string
    {
        // First try to get from company ID in request attributes (set by DetectSubdomain middleware)
        $companyId = $request->attributes->get('stock_audit_company_id');
        if ($companyId) {
            $company = app(CompanyRepositoryInterface::class)
                ->findById(CompanyId::fromInt($companyId));
            if ($company && $company->getSubdomain()) {
                return $company->getSubdomain();
            }
        }

        // Fallback: extract from host
        $host = $request->getHost();
        if (preg_match('/^(?:www\.)?([a-z0-9-]+)\.mygrownet\.com$/i', $host, $matches)) {
            return strtolower($matches[1]);
        }

        return 'stockflow';
    }
}
