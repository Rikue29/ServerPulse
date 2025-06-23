<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Add a fallback route that will handle SPA-style navigation
        // This ensures direct URLs like /analytics, /settings, and /user work
        Route::fallback(function ($uri) {
            if (in_array($uri, ['analytics', 'settings', 'user'])) {
                return view($uri);
            }
            
            return abort(404);
        });
    }
}
