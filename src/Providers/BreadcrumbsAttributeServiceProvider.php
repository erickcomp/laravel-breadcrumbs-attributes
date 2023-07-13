<?php

namespace ErickComp\BreadcrumbAttributes\Providers;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

use ErickComp\BreadcrumbAttributes\BladeComponents\Breadcrumbs as BreadcrumbsComponent;
use ErickComp\BreadcrumbAttributes\Commands\CacheBreadcrumbsCommand;
use ErickComp\BreadcrumbAttributes\Commands\ClearBreadcrumbsCacheCommand;
use ErickComp\BreadcrumbAttributes\CrumbBasket;



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
        $this->registerBreadcrumbsBladeComponent();
    }

    protected function registerApplicationMacros()
    {
        Application::macro('getCachedErickCompLaravelAttributesBreadcrumbsPath', function () {
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

    protected function registerBreadcrumbsBladeComponent()
    {
        Blade::component('erickcomp-breadcrumbs', BreadcrumbsComponent::class);
    }
}
