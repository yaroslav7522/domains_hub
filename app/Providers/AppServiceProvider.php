<?php

namespace App\Providers;

use App\Contracts\BinanceSpotServiceInterface;
use App\Contracts\NotificationServiceInterface;
use App\Services\BinanceSpotService;
use App\Services\EmailNotificationService;
use App\Services\NotificationServiceFactory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NotificationServiceInterface::class, EmailNotificationService::class);
        $this->app->singleton(NotificationServiceFactory::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
