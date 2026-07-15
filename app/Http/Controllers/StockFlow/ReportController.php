<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('StockFlow/Reports/Index');
    }
}
