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
            $siteInfo = \CMS\SiteManager\Models\CmsKit\SiteInformation::first() ?? new \CMS\SiteManager\Models\CmsKit\SiteInformation([
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
                \CMS\SiteManager\Console\Commands\PublishOverridesCommand::class,
            ]);
        }

        // Register Observers
        $this->registerSitemapObservers();

        if (config('cms-kit.url_redirects.middleware_enabled', true)) {
            $this->app->make(\Illuminate\Routing\Router::class)->prependMiddlewareToGroup(
                config('cms-kit.url_redirects.web_middleware_group', 'web'),
                \CMS\SiteManager\Http\Middleware\ApplyUrlRedirects::class
            );
        }

        // Publish Assets
        $this->publishes([
            __DIR__ . '/../resources/css/sitemap.css' => public_path('vendor/cms-kit/css/sitemap.css'),
            __DIR__ . '/../resources/css/cms-premium.css' => public_path('vendor/cms-kit/css/cms-premium.css'),
            __DIR__ . '/../resources/css/cms-auth.css' => public_path('vendor/cms-kit/css/cms-auth.css'),
            __DIR__ . '/../resources/css/cms-modules.css' => public_path('vendor/cms-kit/css/cms-modules.css'),
        ], 'cms-kit-assets');

        // Publish default static translation JSON (optional; English is also bootstrapped from the package if missing)
        $staticLangTarget = function_exists('lang_path')
            ? lang_path(trim((string) config('cms-kit.static_translations.subdirectory', 'cms-static'), '/\\'))
            : resource_path('lang/' . trim((string) config('cms-kit.static_translations.subdirectory', 'cms-static'), '/\\'));
        $this->publishes([
            __DIR__ . '/../resources/lang/cms-static' => $staticLangTarget,
        ], 'cms-kit-static-lang');

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

        // Prefer app overrides when published copies exist.
        $this->registerAppOverrides();

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
                'model' => \CMS\SiteManager\Models\CmsKit\Admin::class,
            ],
            'auth.passwords.cms_admins' => [
                'provider' => 'cms_admins',
                'table' => 'password_resets',
                'expire' => 60,
                'throttle' => 60,
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

    protected function registerAppOverrides(): void
    {
        $this->registerOverrideAliases('Models/CmsKit', app()->getNamespace() . 'Models\\CmsKit\\');
        $this->registerOverrideAliases('Http/Controllers/CmsKit', app()->getNamespace() . 'Http\\Controllers\\CmsKit\\');
    }

    protected function registerOverrideAliases(string $relativePath, string $appNamespace): void
    {
        $directory = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $packageNamespace = 'CMS\\SiteManager\\' . str_replace('/', '\\', $relativePath) . '\\';

        foreach (glob($directory . DIRECTORY_SEPARATOR . '*.php') ?: [] as $file) {
            $className = pathinfo($file, PATHINFO_FILENAME);
            $overrideClass = $appNamespace . $className;
            $packageClass = $packageNamespace . $className;

            if (class_exists($overrideClass) && !class_exists($packageClass, false)) {
                class_alias($overrideClass, $packageClass);
            }
        }
    }
}


