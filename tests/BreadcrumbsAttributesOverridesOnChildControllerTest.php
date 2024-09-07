<?php

namespace ErickComp\BreadcrumbAttributes\Tests;

use Closure;
use ErickComp\BreadcrumbAttributes\CrumbBasket;
use ErickComp\BreadcrumbAttributes\Enums\ConfigWhenAlreadyDefined;
use ErickComp\BreadcrumbAttributes\Facades\BreadcrumbsTrail;
use ErickComp\BreadcrumbAttributes\Providers\BreadcrumbsAttributeServiceProvider;
use ErickComp\BreadcrumbAttributes\Tests\TestClasses\Controllers\ControllerWithoutSpatieRoutes;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class BreadcrumbsAttributesOverridesOnChildControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Config::set('erickcomp-laravel-breadcrumbs-attributes.when_already_defined', ConfigWhenAlreadyDefined::Override);
        $this->registerRoutes();

        $this->registerTestBreadcrumbs();
    }

    /** @test */
    public function it_can_override_a_base_controller_breadcrumbr()
    {
        $response = $this->get('base-and-overrides-controllers');

        $response->assertExactJson([
            [
                'label' => 'Breadcrumb from CHILD_CONTROLLER',
                'url' => URL::to('base-and-overrides-controllers')
            ]
        ]);
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

        $rootNamespace = '\\' . __NAMESPACE__ . '\\TestClasses\\ControllersOverrides\\Base';
        $controllersDir = __DIR__ . DIRECTORY_SEPARATOR . 'TestClasses' . DIRECTORY_SEPARATOR . 'ControllersOverrides' . DIRECTORY_SEPARATOR . 'Base';
        $routeRegistrar
            ->useRootNamespace($rootNamespace)
            ->useMiddleware(SubstituteBindings::class)
            ->useBasePath($controllersDir)
            ->registerDirectory($controllersDir);

        $rootNamespace = '\\' . __NAMESPACE__ . '\\TestClasses\\ControllersOverrides\\Override';
        $controllersDir = __DIR__ . DIRECTORY_SEPARATOR . 'TestClasses' . DIRECTORY_SEPARATOR . 'ControllersOverrides' . DIRECTORY_SEPARATOR . 'Override';
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
            . 'ControllersOverrides' . DIRECTORY_SEPARATOR
            . 'Base';

        $controllersDirOverride = __DIR__ . DIRECTORY_SEPARATOR
            . 'TestClasses' . DIRECTORY_SEPARATOR
            . 'ControllersOverrides' . DIRECTORY_SEPARATOR
            . 'Override';

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

    /**
     * Resolve application core configuration implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);
    }

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
