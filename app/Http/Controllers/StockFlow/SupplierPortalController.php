<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        return Inertia::render('StockFlow/SupplierPortal/Dashboard');
    }

    public function orders(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        return Inertia::render('StockFlow/SupplierPortal/Orders');
    }

    public function profile(Request $request)
    {
        $companyId = $request->session()->get('stockflow_company_id');
        return Inertia::render('StockFlow/SupplierPortal/Profile');
    }
}
