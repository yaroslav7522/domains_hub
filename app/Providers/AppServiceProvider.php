<?php

namespace App\Providers;

use App\Contracts\BinanceSpotServiceInterface;
use App\Services\BinanceSpotService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BinanceSpotServiceInterface::class, fn() => new BinanceSpotService(
            config('services.binance.api_key'),
            config('services.binance.api_secret'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
