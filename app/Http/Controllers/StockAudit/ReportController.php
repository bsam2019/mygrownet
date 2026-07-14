<?php

namespace App\Http\Controllers\StockAudit;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('StockAudit/Reports/Index');
    }
}
