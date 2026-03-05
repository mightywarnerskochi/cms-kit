<?php

namespace CMS\SiteManager;

use Illuminate\Support\ServiceProvider;

class SiteManagerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        // Load Routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // Load Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register Middleware
        $this->app['router']->aliasMiddleware('cms.auth', \CMS\SiteManager\Http\Middleware\CmsAuthenticate::class);

        // Load Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'cms-kit');

        // Publish Config
        $this->publishes([
            __DIR__ . '/../config/cms-kit.php' => config_path('cms-kit.php'),
        ], 'cms-kit-config');

        // Publish Views (optional for user customization)
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/cms-kit'),
        ], 'cms-kit-views');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Merge Config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/cms-kit.php', 'cms-kit'
        );
    }
}