<?php

use App\Http\Controllers\StockFlow\LandingController;
use App\Http\Controllers\StockFlow\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| StockFlow Landing Subdomain Routes (stockflow.mygrownet.com)
|--------------------------------------------------------------------------
|
| These routes handle the main StockFlow marketing subdomain at:
| https://stockflow.mygrownet.com
|
| This is the public-facing landing page where potential clients can:
| - Learn about StockFlow
| - Sign up for an account
| - Request a demo
| - Contact sales
|
| Individual client companies get their own subdomains:
| - taradasi.mygrownet.com
| - acmecorp.mygrownet.com
| - etc.
*/

Route::domain('stockflow.mygrownet.com')
    ->middleware('web')
    ->name('stockflow.landing.')
    ->group(function () {
        // Landing page
        Route::get('/', [LandingController::class, 'index'])->name('home');

        // About/Features/Pricing pages (to be implemented)
        // Route::get('/features', [LandingController::class, 'features'])->name('features');
        // Route::get('/pricing', [LandingController::class, 'pricing'])->name('pricing');
        // Route::get('/about', [LandingController::class, 'about'])->name('about');
        // Route::get('/contact', [LandingController::class, 'contact'])->name('contact');

        // Demo/Trial signup (to be implemented)
        // Route::get('/request-demo', [LandingController::class, 'requestDemo'])->name('request-demo');
        // Route::post('/request-demo', [LandingController::class, 'storeDemo'])->name('request-demo.store');

        // Admin login (could be moved to stockflow-admin.php later)
        Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
    });
