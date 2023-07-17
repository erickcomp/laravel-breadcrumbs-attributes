<?php

namespace ErickComp\BreadcrumbAttributes\Tests;

use Closure;
use ErickComp\BreadcrumbAttributes\CrumbBasket;
use ErickComp\BreadcrumbAttributes\Providers\BreadcrumbsAttributeServiceProvider;
use ErickComp\BreadcrumbAttributes\Commands\CacheBreadcrumbsCommand;
use ErickComp\BreadcrumbAttributes\Commands\ClearBreadcrumbsCacheCommand;
use ErickComp\BreadcrumbAttributes\Tests\TestClasses\Controllers\ControllerWithoutSpatieRoutes;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

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
        $this->registerTestBreadcrumbs();
    }

    public function it_can_create_a_breadcrumb_with_simple_label_without_parent()
    {

    }

    protected function getPackageProviders($app)
    {
        return [
            BreadcrumbsAttributeServiceProvider::class,
        ];
    }

    protected function registerTraditionalRoutes()
    {
        $prefix = "tests-ericklimacomp-laravel-breadcrumbs";

        $routes = [
            "$prefix-route001" => [ControllerWithSpatieRoutes::class, 'route001'],
            "$prefix-route002" => [ControllerWithSpatieRoutes::class, 'route002']
        ];

        foreach ($routes as $route => $handler) {
            Route::get($route, $handler);
        }
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

        dd($crumbBasket);
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

    /** @test */
    public function the_provider_can_register_application_macro_getCachedErickCompLaravelAttributesBreadcrumbsPath()
    {
        foreach (self::APPLICATION_MACROS as $macro) {
            $this->assertTrue($this->app->hasMacro($macro), "Macro $macro is not registered");
        }
    }

    /** @test */
    public function the_provider_can_register_breadcrumbs_cache_command()
    {
        $this->assertAppHasCommand(CacheBreadcrumbsCommand::class);
    }

    /** @test */
    public function the_provider_can_register_breadcrumbs_cache_clear_command()
    {
        $this->assertAppHasCommand(ClearBreadcrumbsCacheCommand::class);
    }

    /** @test */
    public function the_provider_can_register_erickcomp_breadcrumbs_component()
    {
        $this->assertAppHasComponent('erickcomp-breadcrumbs');
    }

    /** @test */
    public function the_provider_can_register_crumb_basket_class_as_singleton()
    {
        $msg = 'Class "' . CrumbBasket::class . '" was not registered as singleton in the app container';
        $this->assertTrue($this->app->isShared(CrumbBasket::class), $msg);
    }

    /** @test */
    public function the_provider_can_register_publish_the_config_file()
    {
        $configFile = config_path(self::CONFIG_FILE);

        if (File::exists($configFile)) {
            File::move($configFile, "$configFile.bkp");
        }

        $this->artisan('vendor:publish', [
            '--provider' => BreadcrumbsAttributeServiceProvider::class,
            '--tag' => 'config',
        ])->assertOk();

        $msg = 'The ServiceProvider did not publish the package config file';
        $this->assertFileExists($configFile, $msg);

        if (File::exists("$configFile.bkp")) {
            File::move("$configFile.bkp", $configFile);
        }
    }

    protected function assertAppHasCommand(string $commandClass)
    {
        static $commandClasses = null;

        if ($commandClasses === null) {
            $commandClasses = \array_map(fn($v) => is_object($v) ? get_class($v) : null, Artisan::all());
        }

        $msg = "Command \"$commandClass\" is not registered in the app";
        $this->assertTrue(\in_array($commandClass, $commandClasses), $msg);
    }

    protected function assertAppHasComponent(string $componentAlias)
    {
        static $componentAliases = null;

        if ($componentAliases === null) {
            $componentAliases = Blade::getClassComponentAliases();
        }

        $msg = "Component with alias \"$componentAlias\" is not registered in the app";
        $this->assertTrue(\array_key_exists($componentAlias, $componentAliases), $msg);
    }
}
