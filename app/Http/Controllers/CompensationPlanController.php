<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompensationPlanController extends Controller
{
    /**
     * Display the compensation plan
     */
    public function show()
    {
        // Redirect to the static HTML file
        return redirect('/compensation-plan.html');
    }
}
