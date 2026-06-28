<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class GeopamuController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Geopamu/Home');
    }

    public function services(): Response
    {
        return Inertia::render('Geopamu/Services');
    }

    public function portfolio(): Response
    {
        return Inertia::render('Geopamu/Portfolio');
    }

    public function about(): Response
    {
        return Inertia::render('Geopamu/About');
    }

    public function contact(): Response
    {
        return Inertia::render('Geopamu/Contact');
    }
}
