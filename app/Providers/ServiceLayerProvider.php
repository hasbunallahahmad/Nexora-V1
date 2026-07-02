<?php

namespace App\Providers;

use App\Services\BidangService;
use Illuminate\Support\ServiceProvider;

class ServiceLayerProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(BidangService::class, function ($app) {
            return new BidangService();
        });

        $this->app->bind(
            'App\Services\Contracts\ServiceInterface',
            'App\Services\Base\BaseService'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
