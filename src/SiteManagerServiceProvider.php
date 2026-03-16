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
        // Load Sitemap Configuration (Manual Merge for internal use)
        if (!config()->has('cms.sitemap')) {
            config(['cms.sitemap' => require __DIR__ . '/../config/cms/sitemap.php']);
        }

        // Share Site Information with all views
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $siteInfo = \CMS\SiteManager\Models\SiteInformation::first() ?? new \CMS\SiteManager\Models\SiteInformation([
                'company_name' => config('cms-kit.common.name', 'CMS Kit')
            ]);
            $view->with('siteInfo', $siteInfo);
            $view->with('cmsUser', \Illuminate\Support\Facades\Auth::guard('cms')->user());
        });

        // Load Routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // Load Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register Middleware
        $this->app['router']->aliasMiddleware('cms.auth', \CMS\SiteManager\Http\Middleware\CmsAuthenticate::class);
        $this->app['router']->aliasMiddleware('cms.permission', \CMS\SiteManager\Http\Middleware\CheckCmsPermission::class);

        // Load Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'cms-kit');

        // Publish Config
        $this->publishes([
            __DIR__ . '/../config/cms-kit.php' => config_path('cms-kit.php'),
            __DIR__ . '/../config/cms' => config_path('cms'),
        ], 'cms-kit-config');

        // Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \CMS\SiteManager\Console\Commands\SitemapGenerateCommand::class,
            ]);
        }

        // Register Observers
        $this->registerSitemapObservers();

        // Publish Assets
        $this->publishes([
            __DIR__ . '/../resources/css/sitemap.css' => public_path('vendor/cms-kit/css/sitemap.css'),
            __DIR__ . '/../resources/css/cms-premium.css' => public_path('vendor/cms-kit/css/cms-premium.css'),
            __DIR__ . '/../resources/css/cms-auth.css' => public_path('vendor/cms-kit/css/cms-auth.css'),
            __DIR__ . '/../resources/css/cms-modules.css' => public_path('vendor/cms-kit/css/cms-modules.css'),
        ], 'cms-kit-assets');

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

        // Configure Guard
        $this->setupAuth();
    }

    protected function setupAuth()
    {
        config([
            'auth.guards.cms' => [
                'driver' => 'session',
                'provider' => 'cms_admins',
            ],
            'auth.providers.cms_admins' => [
                'driver' => 'eloquent',
                'model' => \CMS\SiteManager\Models\Admin::class,
            ],
        ]);
    }

    protected function registerSitemapObservers()
    {
        $models = config('cms.sitemap.models', []);
        foreach ($models as $key => $value) {
            $model = is_numeric($key) ? $value : $key;
            if (class_exists($model)) {
                $model::observe(\CMS\SiteManager\Observers\SitemapObserver::class);
            }
        }
    }
}