<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        return Inertia::render('Welcome', [
            'categories' => Category::all()
        ]);
    }

    public function about()
    {
        return Inertia::render('About');
    }

    public function investment()
    {
        return Inertia::render('Investment', [
            'categories' => Category::with('investments')->get()
        ]);
    }

    public function contact()
    {
        return Inertia::render('Contact');
    }

    public function privacy()
    {
        return Inertia::render('Privacy');
    }

    public function terms()
    {
        return Inertia::render('Terms');
    }
}
