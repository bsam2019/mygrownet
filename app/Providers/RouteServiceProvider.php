<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     * After login, users are redirected to the dashboard.
     */
    public const HOME = '/dashboard';

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
                
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
                
            Route::middleware('web')
                ->group(base_path('routes/manager.php'));
                
            Route::middleware('web')
                ->group(base_path('routes/settings.php'));
                
            Route::middleware('web')
                ->group(base_path('routes/employee-portal.php'));
                
            Route::middleware('web')
                ->group(base_path('routes/growbiz.php'));
                
            Route::middleware('web')
                ->group(base_path('routes/bizboost.php'));
                
            Route::middleware('web')
                ->group(base_path('routes/inventory.php'));
                
            Route::middleware('web')
                ->group(base_path('routes/pos.php'));
                
            Route::middleware('web')
                ->group(base_path('routes/lifeplus.php'));
                
            Route::middleware('web')
                ->group(base_path('routes/marketplace.php'));
                
            // GrowBuilder subdomain routes - must be loaded for subdomain handling
            Route::middleware('web')
                ->group(base_path('routes/subdomain.php'));
        });
    }
}
