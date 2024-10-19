<?php

namespace ErickComp\BreadcrumbAttributes\Tests;

use Closure;
use ErickComp\BreadcrumbAttributes\CrumbBasket;
use ErickComp\BreadcrumbAttributes\Enums\ConfigWhenAlreadyDefined;
use ErickComp\BreadcrumbAttributes\Facades\BreadcrumbsTrail;
use ErickComp\BreadcrumbAttributes\Providers\BreadcrumbsAttributeServiceProvider;
use ErickComp\BreadcrumbAttributes\Tests\TestClasses\Controllers\ControllerWithoutSpatieRoutes;
use ErickComp\BreadcrumbAttributes\Tests\TestClasses\ControllersInheritance\Inherited\ControllerOverrideWithInheritedBreadcrumbsAttributes;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class BreadcrumbsAttributesInheritanceTest extends TestCase
{
    /** @test */
    public function it_can_inherit_breadcrumb_when_config_is_set_to_true()
    {
        Config::set('erickcomp-laravel-breadcrumbs-attributes.inherit_breadcrumb_definition_from_parent_method', true);
        $this->registerRoutes();
        $this->registerTestBreadcrumbs();

        $registeredCrumbs = $this->getRegisteredCrumbs();

        $response = $this->get('base-controller-which-will-get-breadcrumbs-inherited-by-child');

        $response->assertExactJson([
            'controller_action' => ControllerOverrideWithInheritedBreadcrumbsAttributes::class . '::test',
            'crumbs' => [[
                'label' => 'Breadcrumb defined at a BASE_CONTROLLER',
                'url' => URL::to('base-controller-which-will-get-breadcrumbs-inherited-by-child')
            ]]
        ]);
    }

    /** @test */
    public function it_cannnot_inherit_breadcrumb_when_config_is_set_to_false()
    {
        Config::set('erickcomp-laravel-breadcrumbs-attributes.inherit_breadcrumb_definition_from_parent_method', false);
        $this->registerRoutes();
        $this->registerTestBreadcrumbs();

        $expectedResponseExceptionMessage = 'Error building breadcrumb trail: Could not find crumb for the controller action "' . ControllerOverrideWithInheritedBreadcrumbsAttributes::class . '@test"';
        $response = $this->get('base-controller-which-will-get-breadcrumbs-inherited-by-child');
        
        $this->assertEquals($response->exception->getMessage(), $expectedResponseExceptionMessage);
    }

    protected function getPackageProviders($app)
    {
        return [
            BreadcrumbsAttributeServiceProvider::class,
        ];
    }

    protected function registerRoutes()
    {
        /** @var \Spatie\RouteAttributes\RouteRegistrar $routeRegistrar */
        $routeRegistrar = $this->app->make(\Spatie\RouteAttributes\RouteRegistrar::class);

        $rootNamespace = '\\' . __NAMESPACE__ . '\\TestClasses\\ControllersInheritance\\Base';
        $controllersDir = __DIR__ . DIRECTORY_SEPARATOR . 'TestClasses' . DIRECTORY_SEPARATOR . 'ControllersInheritance' . DIRECTORY_SEPARATOR . 'Base';
        $routeRegistrar
            ->useRootNamespace($rootNamespace)
            ->useMiddleware(SubstituteBindings::class)
            ->useBasePath($controllersDir)
            ->registerDirectory($controllersDir);

        $rootNamespace = '\\' . __NAMESPACE__ . '\\TestClasses\\ControllersInheritance\\Inherited';
        $controllersDir = __DIR__ . DIRECTORY_SEPARATOR . 'TestClasses' . DIRECTORY_SEPARATOR . 'ControllersInheritance' . DIRECTORY_SEPARATOR . 'Inherited';
        $routeRegistrar
            ->useRootNamespace($rootNamespace)
            ->useMiddleware(SubstituteBindings::class)
            ->useBasePath($controllersDir)
            ->registerDirectory($controllersDir);
    }

    protected function registerTestBreadcrumbs()
    {
        $controllersDirBase = __DIR__ . DIRECTORY_SEPARATOR
            . 'TestClasses' . DIRECTORY_SEPARATOR
            . 'ControllersInheritance' . DIRECTORY_SEPARATOR
            . 'Base';

        $controllersDirOverride = __DIR__ . DIRECTORY_SEPARATOR
            . 'TestClasses' . DIRECTORY_SEPARATOR
            . 'ControllersInheritance' . DIRECTORY_SEPARATOR
            . 'Inherited';

        $crumbBasket = new CrumbBasket(
            $this->app,
            $this->app->make(\Illuminate\Filesystem\Filesystem::class)
        );

        /** @var Closure $setter */
        $setter = function ($attr, $value) {
            $this->$attr = $value;
        };

        /** @var Closure $caller */
        $crumbBasketCaller = function ($method, ...$args) {
            $this->$method(...$args);
        };

        $crumbBasketCaller->call($crumbBasket, 'gatherCrumbsFromDirectories', [$controllersDirBase, $controllersDirOverride]);

        $this->app->singleton(CrumbBasket::class, function () use ($crumbBasket) {
            return $crumbBasket;
        });
    }

    // /**
    //  * Resolve application core configuration implementation.
    //  *
    //  * @param  \Illuminate\Foundation\Application  $app
    //  *
    //  * @return void
    //  */
    // protected function resolveApplicationConfiguration($app)
    // {
    //     parent::resolveApplicationConfiguration($app);
    // }

    protected function assertBreadcrumbIsRegistered(string $name)
    {
        static $registeredBreadcrumbs = null;

        if ($registeredBreadcrumbs === null) {
            $registeredBreadcrumbs = $this->getRegisteredCrumbs();
        }

        $msg = "Breadcrumbs \"$name\" is not in the Crumb Basket";
        $this->assertTrue(\array_key_exists($name, $registeredBreadcrumbs), $msg);
    }

    protected function getRegisteredCrumbs(): array
    {
        $this->registerTestBreadcrumbs();
        $basket = BreadcrumbsTrail::getCrumbBasket();
        $basket->gatherCrumbsOntoBasket();

        return (function () {
            return $this->crumbs;
        })->call($basket);
    }
}
