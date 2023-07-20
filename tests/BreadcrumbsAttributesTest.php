<?php

namespace ErickComp\BreadcrumbAttributes\Tests;

use Closure;
use ErickComp\BreadcrumbAttributes\CrumbBasket;
use ErickComp\BreadcrumbAttributes\Facades\BreadcrumbsTrail;
use ErickComp\BreadcrumbAttributes\Providers\BreadcrumbsAttributeServiceProvider;
use ErickComp\BreadcrumbAttributes\Tests\TestClasses\Controllers\ControllerWithoutSpatieRoutes;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class BreadcrumbsAttributesTest extends TestCase
{
    private const CONFIG_FILE = 'erickcomp-laravel-breadcrumbs-attributes.php';
    private const APPLICATION_MACROS = [
        'getCachedErickCompLaravelAttributesBreadcrumbsPath'
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->registerTraditionalRoutes();
        $this->registerSpatieRoutes();

        $this->registerTestBreadcrumbs();
    }

    /** @test */
    public function it_can_register_a_breadcrumb_without_parent()
    {
        $this->assertBreadcrumbIsRegistered('home');
    }

    /** @test */
    public function it_can_generate_a_breadcrumb_without_parent()
    {
        $response = $this->get('/');

        $response->assertExactJson([
            [
                'label' => 'Home',
                'url' => URL::to('/')
            ]
        ]);
    }

    /** @test */
    public function it_can_register_a_breadcrumb_with_a_simple_label()
    {
        $this->assertBreadcrumbIsRegistered('home.aSimple');
    }

    /** @test */
    public function it_can_generate_a_breadcrumb_with_a_simple_label()
    {
        $response = $this->get('simple');

        $response->assertExactJson([
            [
                'label' => 'Home',
                'url' => URL::to('/')
            ],
            [
                'label' => 'Simple',
                'url' => URL::to('simple')
            ]
        ]);
    }

    /** @test */
    public function it_can_register_a_breadcrumb_with_an_action_param()
    {
        $this->assertBreadcrumbIsRegistered('home.aActionParam');
    }

    /** @test */
    public function it_can_generate_a_breadcrumb_with_an_action_param()
    {
        $response = $this->get('action-param/Param1PassedInRouteSegment');

        $response->assertExactJson([
            [
                'label' => 'Home',
                'url' => URL::to('/')
            ],
            [
                'label' => 'Param1PassedInRouteSegment',
                'url' => URL::to('action-param/Param1PassedInRouteSegment')
            ]
        ]);
    }

    /** @test */
    public function it_can_register_a_breadcrumb_with_an_action_param_method()
    {
        $this->assertBreadcrumbIsRegistered('home.aActionParamMethod');
    }

    /** @test */
    public function it_can_generate_a_breadcrumb_with_an_action_param_method()
    {
        $response = $this->get('action-param-method');

        $response->assertExactJson([
            [
                'label' => 'Home',
                'url' => URL::to('/')
            ],
            [
                'label' => URL::to('action-param-method'),
                'url' => URL::to('action-param-method')
            ]
        ]);
    }

    /** @test */
    public function it_can_register_a_breadcrumb_with_an_action_param_property()
    {
        $this->assertBreadcrumbIsRegistered('home.aActionParamProperty');
    }

    /** @test */
    public function it_can_generate_a_breadcrumb_with_an_action_param_property()
    {
        $response = $this->get('action-param-property');

        $response->assertExactJson([
            [
                'label' => 'Home',
                'url' => URL::to('/')
            ],
            [
                'label' => 'action-param-property',
                'url' => URL::to('action-param-property')
            ]
        ]);
    }

    /** @test */
    public function it_can_register_a_breadcrumb_with_a_concat_label()
    {
        $this->assertBreadcrumbIsRegistered('home.aConcatLabel');
    }

    /** @test */
    public function it_can_generate_a_breadcrumb_with_a_concat_label()
    {
        $response = $this->get('concat-label');

        $response->assertExactJson([
            [
                'label' => 'Home',
                'url' => URL::to('/')
            ],
            [
                'label' => 'Concat part 1|Concat part 2',
                'url' => URL::to('concat-label')
            ]
        ]);
    }

    /** @test */
    public function it_can_register_a_breadcrumb_with_an_eval_crumb()
    {
        $this->assertBreadcrumbIsRegistered('home.aEvalCrumb');
    }

    /** @test */
    public function it_can_generate_a_breadcrumb_with_an_eval_crumb()
    {
        $response = $this->get('eval-crumb/huehuehue/ErickComp');

        $response->assertExactJson([
            [
                'label' => 'Home',
                'url' => URL::to('/')
            ],
            [
                'label' => 'huehuehue: ERICKCOMP',
                'url' => URL::to('eval-crumb/huehuehue/ErickComp')
            ]
        ]);
    }

    /** @test */
    public function it_can_register_a_breadcrumb_using_the_spatie_route_attribute_name()
    {
        $this->assertBreadcrumbIsRegistered('home.spatie-get-method-no-name');
    }

    public function it_can_register_a_breadcrumb_using_the_spatie_route_attribute_and_custom_name()
    {
        $this->assertBreadcrumbIsRegistered('home.spatie-get-simple-named');
    }

    /** @test */
    public function it_render_a_breadcrumb_trail_using_the_component()
    {
        $response = $this->get('action-param/DummyParamInSegment/render-component');
        $urlToRoot = URL::to('/');
        
        $stubFile = __DIR__ . \DIRECTORY_SEPARATOR . __FUNCTION__ . '.stub';
        $expectedHtml = \sprintf(\file_get_contents($stubFile), $urlToRoot, $urlToRoot);

        $response->assertContent($expectedHtml);
    }

    protected function getPackageProviders($app)
    {
        return [
            BreadcrumbsAttributeServiceProvider::class,
        ];
    }

    protected function registerTraditionalRoutes()
    {
        $prefix = "ericklimacomp-";

        $routesStrs = [
            '/' => 'home',
            'simple' => 'aSimple',
            'action-param/{param1}' => 'aActionParam',
            'action-param-method' => 'aActionParamMethod',
            'action-param-property' => 'aActionParamProperty',
            'concat-label' => 'aConcatLabel',
            'eval-crumb/{param1}/{fake_model?}' => 'aEvalCrumb',
            'action-param/{param1}/render-component' => 'aReturnComponent'
        ];

        foreach ($routesStrs as $route => $method) {
            Route::get("$route", [ControllerWithoutSpatieRoutes::class, $method])
                ->name("$prefix-$method")
                ->middleware(SubstituteBindings::class);
        }
    }

    protected function registerSpatieRoutes()
    {
        /** @var \Spatie\RouteAttributes\RouteRegistrar $routeRegistrar */
        $routeRegistrar = $this->app->make(\Spatie\RouteAttributes\RouteRegistrar::class);

        $rootNamespace = '\\' . __NAMESPACE__ . '\\TestClasses\\Controllers';
        $controllersDir = __DIR__ . DIRECTORY_SEPARATOR . 'TestClasses' . DIRECTORY_SEPARATOR . 'Controllers';
        $routeRegistrar
            ->useRootNamespace($rootNamespace)
            ->useMiddleware(SubstituteBindings::class)
            ->useBasePath($controllersDir)
            ->registerDirectory($controllersDir);
    }

    protected function registerTestBreadcrumbs()
    {
        $testDir = __DIR__ . DIRECTORY_SEPARATOR
            . 'TestClasses' . DIRECTORY_SEPARATOR
            . 'Controllers';

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

        $crumbBasketCaller->call($crumbBasket, 'gatherCrumbsOfDirectories', [$testDir]);

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
