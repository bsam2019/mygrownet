<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Domain\Core\Models\Application;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $descriptions = [
            'bms' => 'Build and manage brand websites, landing pages, and digital content with an intuitive CMS.',
            'stockflow' => 'Complete inventory management with stock tracking, audits, and purchase order management.',
            'growfinance' => 'Financial management tools for budgeting, invoicing, and expense tracking.',
            'bizdocs' => 'Document management and collaboration platform for your business.',
            'growbuilder' => 'Drag-and-drop website builder to create stunning sites without coding.',
            'growmart' => 'Multi-vendor e-commerce marketplace to buy and sell products online.',
            'grownet' => 'Your personal digital wealth hub — learn, earn, and grow with MyGrowNet.',
            'lifeplus' => 'Health and wellness platform with appointment booking and wellness tracking.',
            'zamstay' => 'Accommodation booking platform for hotels, lodges, and short-term rentals.',
            'quick-invoice' => 'Create and send professional invoices, track payments, and manage billing.',
            'growstorage' => 'Secure cloud storage for your business files and documents.',
        ];

        $apps = Application::where('is_active', true)
            ->where('is_visible', true)
            ->where('slug', '!=', 'primeedge')
            ->orderBy('name')
            ->get(['name', 'slug', 'type', 'url', 'category'])
            ->map(fn($app) => array_merge($app->toArray(), [
                'description' => $descriptions[$app->slug] ?? '',
            ]));

        return Inertia::render('Welcome', [
            'featuredApps' => $apps,
            'stats' => [
                'businesses' => ['value' => '2,000+', 'label' => 'Active Businesses'],
                'apps' => ['value' => '12', 'label' => 'Integrated Apps'],
                'uptime' => ['value' => '99.9%', 'label' => 'Platform Uptime'],
                'support' => ['value' => '24/7', 'label' => 'Support Available'],
            ],
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
