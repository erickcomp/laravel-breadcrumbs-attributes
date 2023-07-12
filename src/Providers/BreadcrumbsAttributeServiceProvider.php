<?php

namespace ErickComp\BreadcrumbAttributes\Providers;

use ErickComp\BreadcrumbAttributes\Commands\CacheBreadcrumbsCommand;
use ErickComp\BreadcrumbAttributes\Commands\ClearBreadcrumbsCacheCommand;
use ErickComp\BreadcrumbAttributes\CrumbBasket;
use Illuminate\Foundation\Application;

use Illuminate\Support\ServiceProvider;

class BreadcrumbsAttributeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        /** @var CrumbBasket */
        $crumbBasket = $this->app->make(CrumbBasket::class);
        $crumbBasket->gatherCrumbsOntoBasket();
    }

    public function register()
    {
        $this->app->singleton(CrumbBasket::class);

        $this->registerApplicationMacros();
        $this->registerBreadcrumbsCommands();
    }

    protected function registerApplicationMacros()
    {
        Application::macro('getCachedErickCompBreadcrumbsPath', function () {
            return $this->normalizeCachePath(
                CrumbBasket::BREADCRUMBS_CACHE_FILE_KEY,
                CrumbBasket::BREADCRUMBS_CACHE_FILE_DEFAULT
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
}
