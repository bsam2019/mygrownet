<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HubLandingController extends Controller
{
    public function show(Request $request): Response
    {
        return Inertia::render('GrowStream/LandingHub', [
            'appName' => 'GrowStream Hub',
            'pricingTiers' => [
                [
                    'name' => 'Starter Hub',
                    'price' => 'K0',
                    'period' => 'Free Forever',
                    'description' => 'Perfect for individual tutors and private educators starting out.',
                    'storage' => '250 Mins Video Storage',
                    'bandwidth' => '25 GB Streaming Delivery',
                    'domain' => 'Hosted Subdomain (acme.growstream.app)',
                    'byop' => false,
                    'cta' => 'Create Free Hub',
                ],
                [
                    'name' => 'Professional Hub',
                    'price' => 'K450',
                    'period' => '/ month',
                    'description' => 'Ideal for established online tuition academies & schools.',
                    'storage' => '1,500 Mins Video Storage',
                    'bandwidth' => '200 GB Streaming Delivery',
                    'domain' => 'Custom Domain (www.mymathstuition.com)',
                    'byop' => true,
                    'is_popular' => true,
                    'cta' => 'Start 14-Day Free Trial',
                ],
                [
                    'name' => 'Enterprise Hub',
                    'price' => 'Custom',
                    'period' => 'Contact Sales',
                    'description' => 'For universities, colleges, and large training institutes.',
                    'storage' => 'Unlimited Storage & Streaming',
                    'bandwidth' => 'Dedicated Bandwidth & Moodle LMS',
                    'domain' => 'Multi-domain & Dedicated SSL',
                    'byop' => true,
                    'cta' => 'Contact Sales',
                ],
            ],
        ]);
    }
}
