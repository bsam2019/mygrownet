<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Repositories\CompanyRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/** @deprecated Use App\Http\Controllers\Platform\UnifiedAuthController instead. Will be removed after Phase 8 validation. */
class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        $companyId = $request->attributes->get('stockflow_company_id');
        $company = $companyId
            ? app(CompanyRepositoryInterface::class)->findById(CompanyId::fromInt($companyId))
            : null;

        return Inertia::render('StockFlow/Login', [
            'company' => $company ? $company->toArray() : null,
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('stockflow')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect('/');
        }

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
        $companyId = $request->attributes->get('stockflow_company_id');
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
