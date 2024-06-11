<?php

namespace ErickComp\BreadcrumbAttributes;

use ErickComp\BreadcrumbAttributes\Util\LazyReflectionMethod;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Config;
use SplFileInfo;
use ReflectionClass;
use ReflectionMethod;
use ReflectionAttribute;

use Illuminate\Support\Arr;
use Symfony\Component\Finder\Finder;

use ErickComp\BreadcrumbAttributes\Attributes\Breadcrumb;
use ErickComp\BreadcrumbAttributes\Facades\FileBreadcrumb;

class CrumbBasket
{
    public const BREADCRUMBS_CONFIG_KEY = 'erickcomp-laravel-breadcrumbs-attributes';
    public const BREADCRUMBS_CONTROLLERS_DIRS_CONFIG_KEY = 'controller_directories';
    public const BREADCRUMBS_FILES_CONFIG_KEY = 'breadcrumbs_files';
    public const BREADCRUMBS_CONTROLLERS_DIRS_FULL_CONFIG_KEY = self::BREADCRUMBS_CONFIG_KEY . '.' . self::BREADCRUMBS_CONTROLLERS_DIRS_CONFIG_KEY;
    public const BREADCRUMBS_DEFAULT_CONFIG_FILE = __DIR__ . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . self::BREADCRUMBS_CONFIG_KEY . '.php';
    public const BREADCRUMBS_CACHE_FILE_KEY = 'ERICKCOMP_LARAVEL_BREADCRUMBS_ATTRIBUTES_CACHE';
    public const BREADCRUMBS_CACHE_FILE_DEFAULT = 'cache/erickcomp-laravel-breadcrumbs-attributes';
    private const SPATIE_CONTROLLER_DIRECTORIES_CONFIG_KEY = 'route-attributes.directories';

    /** @var array<string, Crumb> $crumbs */
    protected array $crumbs = [];
    // private array $reflCrumbsCache;
    private const SPATIE_ROUTE_ATTRIBUTE_FQN = '\\Spatie\\RouteAttributes\\Attributes\\Route';

    public function __construct(
        protected Application $application,
        protected Filesystem $fileSystem
    ) {

    }

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
        $this->gatherCrumbsFromFiles($this->);

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

    protected function gatherCrumbsFromFiles(string|array $directories): void
    {
        $directories = Arr::wrap($directories);

        $files = (new Finder())->files()->name('*.php')->in($directories)->sortByName();

        // $this->reflCrumbsCache = [];
        collect($files)->each(fn(SplFileInfo $file) => $this->gatherCrumbsOfFile($file));
        // $this->reflCrumbsCache = [];
    }

    protected function gatherCrumbsOfFile(SplFileInfo $file): void
    {
        $fullyQualifiedClassName = $this->fullQualifiedClassNameFromFile($file);

        $this->processAttributes($fullyQualifiedClassName);
    }

    protected function getControllersDirectories(): array
    {
        $defaultConfigs = require self::BREADCRUMBS_DEFAULT_CONFIG_FILE;
        return \array_unique(
            \array_merge(
                $defaultConfigs[self::BREADCRUMBS_CONTROLLERS_DIRS_CONFIG_KEY],
                $this->getSpatieRoutesAttributesControllersDirs(),
                Config::get(self::BREADCRUMBS_CONTROLLERS_DIRS_FULL_CONFIG_KEY, [])
            )
        );
    }

    protected function getBreadcrumbsFiles(): array
    {
        $files = Arr::wrap(config(static::BREADCRUMBS_CONFIG_KEY '.' .static::BREADCRUMBS_FILES_CONFIG_KEY))
        foreach ($files as $file) {
            if(\is_file($file)) {
                require $file;
            }
        }

        
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
            $spatieRoutesAttributes = $reflMethod->getAttributes(self::SPATIE_ROUTE_ATTRIBUTE_FQN, ReflectionAttribute::IS_INSTANCEOF) ?? [];

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

            if (\array_key_exists($crumbAttrInstance->name, $this->crumbs)) {
                $currentCrumbFile = $reflMethod->getFileName();
                $currentCrumbFileLine = $reflMethod->getStartLine();

                // $definedCrumbData = $this->reflCrumbsCache[$crumbAttrInstance->name];
                // $crumbDefinedFile = $definedCrumbData['ReflectionMethod']->getFileName();
                // $crumbDefinedLine = $definedCrumbData['ReflectionMethod']->getStartLine();
                $definedReflMethod = $this->crumbs[$crumbAttrInstance->name]->reflControllerAction;
                $crumbDefinedFile = $definedReflMethod->get()->getFileName();
                $crumbDefinedLine = $definedReflMethod->get()->getStartLine();


                $errMsg = "The breadcrumb named \"{$crumbAttrInstance->name}\" cannot be defined at "
                    . "$currentCrumbFile:$currentCrumbFileLine because it's "
                    . "already been defined at $crumbDefinedFile:$crumbDefinedLine";

                throw new \LogicException($errMsg);
            }

            $this->crumbs[$crumbAttrInstance->name] = new Crumb(
                $crumbAttrInstance,
                LazyReflectionMethod::fromReflectionMethod($reflMethod)
            );
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

        if (\is_array($controllerAction)) {
            $controllerAction = \implode('@', $controllerAction);
        }

        if (\str_starts_with($controllerAction, '\\')) {
            $controllerAction = \substr($controllerAction, 1);
        }

        foreach ($this->crumbs as $crumb) {
            if ($crumb->getControllerAction() === $controllerAction) {
                return $crumb;
            }
        }

        return null;
    }

}
