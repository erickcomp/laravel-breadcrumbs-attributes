<?php

namespace ErickComp\BreadcrumbAttributes;

use ErickComp\BreadcrumbAttributes\Attributes\Breadcrumb;
use ErickComp\BreadcrumbAttributes\Enums\ConfigWhenAlreadyDefined;
use ErickComp\BreadcrumbAttributes\Providers\BreadcrumbsAttributeServiceProvider;
use ErickComp\BreadcrumbAttributes\Util\LazyReflectionMethod;
use ErickComp\BreadcrumbAttributes\Util\LazyReflectionMethodFromRouteName;
use ErickComp\BreadcrumbAttributes\Util\LazyReflectionMethodInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\Facades\Log;

class CrumbBasket
{
    public const BREADCRUMBS_CONFIG_KEY = 'erickcomp-laravel-breadcrumbs-attributes';
    public const BREADCRUMBS_CONTROLLERS_DIRS_CONFIG_KEY = 'controller_directories';
    public const BREADCRUMBS_CONTROLLERS_DIRS_FULL_CONFIG_KEY = self::BREADCRUMBS_CONFIG_KEY . '.' . self::BREADCRUMBS_CONTROLLERS_DIRS_CONFIG_KEY;
    public const BREADCRUMBS_FILES_CONFIG_KEY = 'breadcrumbs_files';
    public const BREADCRUMBS_FILES_FULL_CONFIG_KEY = self::BREADCRUMBS_CONFIG_KEY . '.' . self::BREADCRUMBS_FILES_CONFIG_KEY;
    public const BREADCRUMBS_INHERIT_BREADCRUMB_DEFINITION_FROM_PARENT_METHOD_CONFIG_KEY = 'inherit_breadcrumb_definition_from_parent_method';
    public const BREADCRUMBS_INHERIT_BREADCRUMB_DEFINITION_FROM_PARENT_METHOD_FULL_CONFIG_KEY = self::BREADCRUMBS_CONFIG_KEY . '.' . self::BREADCRUMBS_INHERIT_BREADCRUMB_DEFINITION_FROM_PARENT_METHOD_CONFIG_KEY;
    public const BREADCRUMBS_DEFAULT_CONFIG_FILE = __DIR__ . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . self::BREADCRUMBS_CONFIG_KEY . '.php';
    public const BREADCRUMBS_CACHE_FILE_KEY = 'ERICKCOMP_LARAVEL_BREADCRUMBS_ATTRIBUTES_CACHE';
    public const BREADCRUMBS_CACHE_FILE_DEFAULT = 'cache/erickcomp-laravel-breadcrumbs-attributes';
    private const SPATIE_CONTROLLER_DIRECTORIES_CONFIG_KEY = 'route-attributes.directories';

    /** @var array<string, Crumb> $crumbs */
    protected array $crumbs = [];

    /** @var array<string, Crumb> $crumbs */
    protected array $nonAttributeCrumbs = [];

    // private array $reflCrumbsCache;
    private const SPATIE_ROUTE_ATTRIBUTE_FQN = '\\Spatie\\RouteAttributes\\Attributes\\Route';

    public function __construct(
        protected Application $application,
        protected Filesystem $fileSystem,
    ) {}

    public function gatherCrumbsOntoBasket(bool $ignoreCache = false)
    {
        if (!empty($this->crumbs)) {
            return;
        }

        if ($this->breadcrumbsAreCached() && !$ignoreCache) {
            $this->crumbs = $this->getCachedCrumbs();

            return;
        }

        $this->gatherCrumbsFromDirectories($this->getControllersDirectories());
        $this->gatherAttributelessCrumbsFromFiles($this->getAttributelessCrumbsFiles());
    }

    /**
     * This method must accept all the arguments of the
     * @see {\ErickCompBreadcrumbAttributesAttributesBreadcrumb:: __construct} method
     * plus a route name. If the name is ommited, the route name will be used on the breadcrumb
     *
     */
    public function putCrumbForRouteName(
        string|\Stringable $routeName,
        string|\Stringable|array $label,
        string|\Stringable|null $parent = null,
        string|\Stringable|null $name = null,
        string|\Stringable|array|null $before = null,
        string|\Stringable|array|null $after = null,
    ) {
        //$route = $this->router->getRoutes()->getByName($routeName);   

        if ($name === null) {
            $name = $routeName;
        }

        $crumbAttrInstance = new Breadcrumb(
            $label,
            $parent,
            $name,
            $before,
            $after,
        );

        $this->addFromBreadcrumbAttributeInstanceAndLazyReflectionMethod(
            $crumbAttrInstance,
            new LazyReflectionMethodFromRouteName($routeName),
        );
    }

    /**
     * This method must accept all the arguments of the
     * @see {\ErickCompBreadcrumbAttributesAttributesBreadcrumb:: __construct} method
     * plus a Controller Action
     *
     */
    public function putCrumbForControllerAction(
        string|array $controllerAction,
        string|\Stringable|array $label,
        string|\Stringable|null $name,
        string|\Stringable|null $parent = null,
        string|\Stringable|array|null $before = null,
        string|\Stringable|array|null $after = null,
    ) {
        $crumbAttrInstance = new Breadcrumb(
            $label,
            $parent,
            $name,
            $before,
            $after,
        );

        $controllerAction = static::normalizeControllerAction($controllerAction);
        [$controllerClass, $controllerMethod] = \explode('@', $controllerAction);
        $lazyReflMethod = new LazyReflectionMethod($controllerClass, $controllerMethod);

        $this->addFromBreadcrumbAttributeInstanceAndLazyReflectionMethod(
            $crumbAttrInstance,
            $lazyReflMethod,
        );
    }

    /**
     * Gets the crumb trail for the give route
     *
     * @return Crumb[]
     */
    public function getCrumbsAttributesTrailForRoute(Route $route): array
    {
        $this->gatherCrumbsOntoBasket();

        $crumbsTrail = [];
        $routeControllerAction = $route->getAction('controller');

        $currentCrumb = $this->getCrumbByControllerAction($routeControllerAction);

        if ($currentCrumb === null) {
            $errMsg = "Error building breadcrumb trail: Could not find crumb for the controller action \"$routeControllerAction\"";

            throw new \LogicException($errMsg);
        }

        do {
            $crumbsTrail[] = $currentCrumb;

            $currentCrumbName = $currentCrumb?->crumbData?->parent;

            if ($currentCrumbName !== null) {
                $currentCrumb = $this->getCrumbByName($currentCrumbName);

                if ($currentCrumb === null) {
                    $errMsg = "Error building breadcrumb trail: Could not find crumb for the name \"$currentCrumbName\"";

                    throw new \LogicException($errMsg);
                }
            }
        } while ($currentCrumbName !== null);

        return \array_reverse($crumbsTrail);
    }

    public function breadcrumbsAreCached(): bool
    {
        return $this->fileSystem->exists($this->getCacheFilePath());
    }

    public function cacheBreadcrumbs()
    {
        $this->clearBreadcrumbsCache();
        $this->gatherCrumbsOntoBasket();
        //$this->fileSystem->put($this->getCacheFilePath(), \var_export($this->crumbs, true));
        $this->fileSystem->put($this->getCacheFilePath(), serialize($this->crumbs));
    }

    public function clearBreadcrumbsCache()
    {
        $this->fileSystem->delete($this->getCacheFilePath());
    }

    protected function gatherCrumbsFromDirectories(string|array $directories): void
    {
        $directories = Arr::wrap($directories);

        $files = (new Finder())->files()->name('*.php')->in($directories)->sortByName();

        // $this->reflCrumbsCache = [];
        collect($files)->each(fn(SplFileInfo $file) => $this->gatherCrumbsOfFile($file));
        // $this->reflCrumbsCache = [];
    }

    protected function gatherAttributelessCrumbsFromFiles(string|array $files): void
    {
        $files = Arr::wrap($files);

        foreach ($files as $file) {
            if (\is_file($file)) {
                include_once $file;

                continue;
            }

            if (\realpath($file) !== \realpath(BreadcrumbsAttributeServiceProvider::defaultAttributelessBreadcrumbsFile())) {
                throw new \RuntimeException("File [$file] which should contain attributeless breadcrumb definitions does not exist");
            }
        }
    }

    /** @return string[] */
    protected function getAttributelessCrumbsFiles(): array
    {
        return config(self::BREADCRUMBS_FILES_FULL_CONFIG_KEY, []);
    }

    protected function gatherCrumbsOfFile(SplFileInfo $file): void
    {
        $fullyQualifiedClassName = $this->fullQualifiedClassNameFromFile($file);

        if ($fullyQualifiedClassName === null) {
            Log::debug("Could not process breadcrumbs attributes of file: [{$file->getPathname()}]");

            return;
        }

        $this->processAttributes($fullyQualifiedClassName);
    }

    protected function getControllersDirectories(): array
    {
        $defaultConfigs = require self::BREADCRUMBS_DEFAULT_CONFIG_FILE;
        return \array_unique(
            \array_merge(
                $defaultConfigs[self::BREADCRUMBS_CONTROLLERS_DIRS_CONFIG_KEY],
                $this->getSpatieRoutesAttributesControllersDirs(),
                Config::get(self::BREADCRUMBS_CONTROLLERS_DIRS_FULL_CONFIG_KEY, []),
            ),
        );
    }

    protected function getBreadcrumbsFiles(): array
    {
        return Arr::wrap(config(static::BREADCRUMBS_CONFIG_KEY . '.' . static::BREADCRUMBS_FILES_CONFIG_KEY));
    }

    protected function getSpatieRoutesAttributesControllersDirs()
    {
        $spatieControllersDirectories = Config::get(self::SPATIE_CONTROLLER_DIRECTORIES_CONFIG_KEY, []);
        $parsedSpatieControllersDirectories = [];

        foreach ($spatieControllersDirectories as $key => $val) {
            $parsedSpatieControllersDirectories[] = \is_int($key) || (\is_string($key) && \is_string($val))
                ? $val
                : $key;
        }

        return $parsedSpatieControllersDirectories;
    }

    protected function getCacheFilePath(): string
    {
        //return $this->application->normalizeCachePath(self::BREADCRUMBS_CACHE_FILE_KEY, self::BREADCRUMBS_CACHE_FILE_DEFAULT);
        return $this->application->getCachedErickCompLaravelAttributesBreadcrumbsPath();
    }

    /**
     * Get the unserialized crumbs from the cache file
     *
     * @return array<string, Crumb>
     */
    protected function getCachedCrumbs(): array
    {
        if (!$this->breadcrumbsAreCached()) {
            return [];
        }

        return unserialize($this->fileSystem->get($this->getCacheFilePath()));
    }

    protected function fullQualifiedClassNameFromFile(SplFileInfo $file): ?string
    {
        $filePath = $file->getRealPath();

        if ($filePath === false) {
            return null;
        }

        $fileContent = \file_get_contents($filePath);

        $regexPattern = '/namespace\s+([^;|{|\s]+).*class\s+([\w]+)/s';
        preg_match($regexPattern, $fileContent, $matches);

        $namespace = isset($matches[1]) ? trim($matches[1]) : '';
        $className = isset($matches[2]) ? trim($matches[2]) : '';

        if (!$className) {
            return null;
        }

        if (!empty($namespace) && !\str_starts_with($namespace, '\\')) {
            $namespace = '\\' . $namespace;
        }

        return "$namespace\\$className";
    }

    protected function processAttributes(string $className)
    {
        if (!class_exists($className)) {
            return;
        }

        $reflClass = new ReflectionClass($className);
        $reflMethods = $reflClass->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($reflMethods as $reflMethod) {
            $crumbsAttributes = $reflMethod->getAttributes(Breadcrumb::class, ReflectionAttribute::IS_INSTANCEOF);

            $spatieRoutesAttributes = \class_exists(self::SPATIE_ROUTE_ATTRIBUTE_FQN)
                ? ($reflMethod->getAttributes(self::SPATIE_ROUTE_ATTRIBUTE_FQN, ReflectionAttribute::IS_INSTANCEOF) ?? [])
                : null;

            if (empty($crumbsAttributes)) {
                continue;
            }

            /** @var ReflectionAttribute $crumbAttr */
            $crumbAttr = $crumbsAttributes[0];
            $spatieRouteAttr = $spatieRoutesAttributes[0] ?? null;

            /** @var Breadcrumb $crumbAttrInstance */
            $crumbAttrInstance = $crumbAttr->newInstance();
            $spatieRoute = $spatieRouteAttr ? $spatieRouteAttr->newInstance() : null;

            if ($crumbAttrInstance->name === null) {
                if ($spatieRoute === null) {
                    $errMsg = "If the method does not contain a "
                        . self::SPATIE_ROUTE_ATTRIBUTE_FQN
                        . " attribute, you must provide a name";

                    throw new \LogicException($errMsg);
                }

                if ($spatieRoute->name === null) {
                    $errMsg = "You must provide a name for your Breadcrumb "
                        . "attribute or the attribute of type "
                        . self::SPATIE_ROUTE_ATTRIBUTE_FQN . " must have a name";

                    throw new \LogicException($errMsg);
                }

                $crumbAttrInstance->name = $spatieRoute->name;
            }

            $this->addFromBreadcrumbAttributeInstanceAndLazyReflectionMethod(
                $crumbAttrInstance,
                //LazyReflectionMethod::fromReflectionMethod($reflMethod)
                new LazyReflectionMethod($className, $reflMethod->name),
            );
        }
    }

    protected function addFromBreadcrumbAttributeInstanceAndLazyReflectionMethod(
        Breadcrumb $crumbAttrInstance,
        LazyReflectionMethodInterface $lazyReflMethod,
    ) {
        if ($crumbAttrInstance->name === null) {
            $errmsg = 'All crumbs must have a name';

            throw new \LogicException($errmsg);
        }

        if (!\array_key_exists($crumbAttrInstance->name, $this->crumbs)) {
            $this->crumbs[$crumbAttrInstance->name] = new Crumb(
                $crumbAttrInstance,
                $lazyReflMethod,
            );

            return;
        }

        /** @var ConfigWhenAlreadyDefined $configWhenAlreadyDefined*/
        $configWhenAlreadyDefined = config('erickcomp-laravel-breadcrumbs-attributes.when_already_defined');

        switch ($configWhenAlreadyDefined->value) {
            case ConfigWhenAlreadyDefined::ThrowException->value:
                if ($lazyReflMethod->isInitialized()) {
                    $currentCrumbFile = $lazyReflMethod->get()->getFileName();
                    $currentCrumbFileLine = $lazyReflMethod->get()->getStartLine();

                    $firstDefinedAt = "at $currentCrumbFile:$currentCrumbFileLine";
                } else {
                    $firstDefinedAt = '';
                }

                $definedReflMethod = $this->crumbs[$crumbAttrInstance->name]->reflControllerAction;
                $crumbDefinedFile = $definedReflMethod->get()->getFileName();
                $crumbDefinedLine = $definedReflMethod->get()->getStartLine();


                $errMsg = "The breadcrumb named \"{$crumbAttrInstance->name}\" cannot be redefined $firstDefinedAt "
                    . "because it's already been defined at $crumbDefinedFile:$crumbDefinedLine";

                throw new \LogicException($errMsg);

            case ConfigWhenAlreadyDefined::Ignore->value:
                break;
            case ConfigWhenAlreadyDefined::Override->value:
                $this->crumbs[$crumbAttrInstance->name] = new Crumb(
                    $crumbAttrInstance,
                    $lazyReflMethod,
                );
                break;
            default:
                throw new \DomainException("Config value [erickcomp-laravel-breadcrumbs-attributes.when_already_defined] must be an instance of [" . ConfigWhenAlreadyDefined::class . "]");
        }
    }

    protected function getCrumbByName(?string $name): ?Crumb
    {
        return $this->crumbs[$name] ?? null;
    }

    protected function getCrumbByControllerAction(array|string|null $controllerAction): ?Crumb
    {
        if (!$controllerAction) {
            return null;
        }

        // if (\is_array($controllerAction)) {
        //     $controllerAction = \implode('@', $controllerAction);
        // }

        // if (\str_starts_with($controllerAction, '\\')) {
        //     $controllerAction = \substr($controllerAction, 1);
        // }

        $controllerAction = static::normalizeControllerAction($controllerAction);
        foreach ($this->crumbs as $crumb) {
            if ($crumb->getControllerAction() === $controllerAction) {
                return $crumb;
            }
        }

        $inheritBreadcrumbDefinitionFromParentMethod = \config(static::BREADCRUMBS_INHERIT_BREADCRUMB_DEFINITION_FROM_PARENT_METHOD_FULL_CONFIG_KEY, false);

        if ($inheritBreadcrumbDefinitionFromParentMethod) {
            [$class, $method] = \explode('@', $controllerAction);

            $parentClass = \get_parent_class($class);

            if ($parentClass !== false) {
                $parentControllerAction = "$parentClass@$method";

                $parentCrumb = $this->getCrumbByControllerAction($parentControllerAction);

                if ($parentCrumb !== null) {
                    $lazyReflMethod = new LazyReflectionMethod($class, $method);
                    $inheritedCrumb = new Crumb($parentCrumb->crumbData, $lazyReflMethod);

                    return $inheritedCrumb;
                }
            }
        }

        //dd($this->crumbs);

        return null;
    }

    protected static function normalizeControllerAction(string|array $controllerAction): string
    {
        if (\is_array($controllerAction)) {
            $controllerAction = \implode('@', $controllerAction);
        }

        if (\str_starts_with($controllerAction, '\\')) {
            $controllerAction = \substr($controllerAction, 1);
        }

        return \str_replace('::', '@', $controllerAction);
    }
}
