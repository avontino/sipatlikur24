<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        
        $forwardedHost = request()->header('X-Forwarded-Host') ?? request()->server('HTTP_X_FORWARDED_HOST');
        $forwardedProto = request()->header('X-Forwarded-Proto') ?? request()->server('HTTP_X_FORWARDED_PROTO');

        if ($forwardedHost) {
            $scheme = ($forwardedProto === 'https' || request()->isSecure()) ? 'https' : 'http';
            \Illuminate\Support\Facades\URL::forceRootUrl("{$scheme}://{$forwardedHost}");
            if ($scheme === 'https') {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            }
        } elseif ($forwardedProto === 'https' || request()->isSecure()) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
