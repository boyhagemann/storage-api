<?php

namespace App\Providers;

use mmghv\LumenRouteBinding\RouteBindingServiceProvider as BaseServiceProvider;

class RouteBindingServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $binder = $this->binder;

        $binder->bind('resource', function($value) {

        });
    }
}
