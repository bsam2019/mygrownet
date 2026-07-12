<?php

namespace App\Http\Controllers\PrimeEdge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PublicController extends Controller
{
    public function landing()
    {
        return Inertia::render('PrimeEdge/Public/Landing');
    }

    public function services()
    {
        return Inertia::render('PrimeEdge/Public/Services', [
            'serviceCategories' => config('modules.primeedge.services'),
        ]);
    }

    public function about()
    {
        return Inertia::render('PrimeEdge/Public/About');
    }

    public function contact()
    {
        return Inertia::render('PrimeEdge/Public/Contact');
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'company' => ['nullable', 'string', 'max:255'],
            'service' => ['nullable', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        Log::info('PrimeEdge contact inquiry', $validated);

        return redirect()->route('primeedge.public.contact')
            ->with('success', 'Thank you! We will get back to you within 24 hours.');
    }
}
