<?php
declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Providers;

use Illuminate\Support\ServiceProvider;
use Scarlett\DMDD\GUI\Services\BackendIntegrationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(abstract: BackendIntegrationService::class, concrete: function ($app): BackendIntegrationService {
            return new BackendIntegrationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
