<?php

namespace App\Providers;

use App\Http\Middleware\EnforceApplicationDomain;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class DomainRoutingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(
        Router $router
    ): void {
        /*
        |--------------------------------------------------------------------------
        | SEPARACIÓN SITIO / APP
        |--------------------------------------------------------------------------
        */

        $router->pushMiddlewareToGroup(
            'web',
            EnforceApplicationDomain::class
        );
    }
}