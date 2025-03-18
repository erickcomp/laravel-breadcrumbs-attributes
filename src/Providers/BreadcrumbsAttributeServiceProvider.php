<?php

namespace ErickComp\BreadcrumbAttributes\Providers;

use ErickComp\BreadcrumbAttributes\BladeComponents\Breadcrumbs as BreadcrumbsComponent;
use ErickComp\BreadcrumbAttributes\Commands\CacheBreadcrumbsCommand;
use ErickComp\BreadcrumbAttributes\Commands\ClearBreadcrumbsCacheCommand;
use ErickComp\BreadcrumbAttributes\CrumbBasket;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BreadcrumbsAttributeServiceProvider extends ServiceProvider
{
    public static function defaultAttributelessBreadcrumbsFile(): string
    {
        return base_path('routes/breadcrumbs.php');
    }


    public function register()
    {
        $this->app->singleton(CrumbBasket::class);

        $this->mergeDefaultConfigs();
        $this->registerApplicationMacros();
        $this->registerBreadcrumbsCommands();
        $this->registerBreadcrumbsBladeComponent();
    }

    public function boot()
    {
        $this->publishConfigFileIfAsked();
        $this->publishAttributelessBreadcrumbsFileIfAsked();
        /** @var CrumbBasket */
        $crumbBasket = $this->app->make(CrumbBasket::class);
        $crumbBasket->gatherCrumbsOntoBasket();

        if (\method_exists($this, 'optimizes')) {
            $this->optimizes(
                optimize: 'erickcomp:laravel-breadcrumbs-attributes:cache',
                clear: 'erickcomp:laravel-breadcrumbs-attributes:clear-cache',
                key: 'breadcrumbs',
            );
        }

    }

    protected function publishConfigFileIfAsked()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $configFile = 'erickcomp-laravel-breadcrumbs-attributes.php';
        $packageConfigFile = __DIR__ . \DIRECTORY_SEPARATOR
            . '..' . \DIRECTORY_SEPARATOR
            . 'config' . \DIRECTORY_SEPARATOR
            . $configFile;

        $this->publishes([$packageConfigFile => \config_path($configFile)], 'config');
    }

    protected function publishAttributelessBreadcrumbsFileIfAsked()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $nonAttributesBreadcrumbsFile = __DIR__ . \DIRECTORY_SEPARATOR
            . '..' . \DIRECTORY_SEPARATOR
            . 'config' . \DIRECTORY_SEPARATOR
            . 'attributeless-breadcrumbs.php';

        $this->publishes([$nonAttributesBreadcrumbsFile => \base_path('routes/breadcrumbs.php')], 'attributeless-breadcrumbs');
    }

    protected function mergeDefaultConfigs()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/erickcomp-laravel-breadcrumbs-attributes.php',
            'erickcomp-laravel-breadcrumbs-attributes',
        );
    }

    protected function registerApplicationMacros()
    {
        Application::macro('getCachedErickCompLaravelAttributesBreadcrumbsPath', function () {
            return $this->normalizeCachePath(
                CrumbBasket::BREADCRUMBS_CACHE_FILE_KEY,
                CrumbBasket::BREADCRUMBS_CACHE_FILE_DEFAULT,
            );
        });
    }

    protected function registerBreadcrumbsCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CacheBreadcrumbsCommand::class,
                ClearBreadcrumbsCacheCommand::class,
            ]);
        }
    }

    protected function registerBreadcrumbsBladeComponent()
    {
        Blade::component('erickcomp-breadcrumbs', BreadcrumbsComponent::class);
    }
}
