<?php

namespace ErickComp\BreadcrumbAttributes\Util;

use Illuminate\Container\Container;
use Illuminate\Routing\Router;
use Illuminate\Routing\ResolvesRouteDependencies;
use Illuminate\Contracts\Routing\UrlGenerator;
use ReflectionMethod;

class ControllerActionRoutesAndParamsResolver
{
    use ResolvesRouteDependencies;

    public function __construct(
        protected Router $router,
        protected UrlGenerator $urlGenerator,
        protected Container $container // used inside "ResolvesRouteDependencies" trait
    ) {
    }

    /**
     *
     * @todo Maybe make the route optional by leveraging the "action" method from
     * url generator and use the already stored metadata
     * to generate urls instead of the route name
     * 
     * @param string $route
     * @return string
     */
    public function resolveRouteUrlWithCurrentRouteParams(string $route): string
    {
        return $this->urlGenerator->route($route, $this->getParametersForRouteFromCurrentRouteParams($route));
    }

    public function resolveControllerActionUrlWithCurrentRouteParams(array|string $controllerAction): string
    {
        if (\is_array($controllerAction)) {
            $controllerAction = \implode('@', $controllerAction);
            if (\str_starts_with($controllerAction, '\\')) {
                $controllerAction = \substr($controllerAction, 1);
            }
        }
        //return $this->urlGenerator->route($route, $this->getParametersForRouteFromCurrentRouteParams($route));
        return $this->urlGenerator->action($controllerAction, $this->getParametersForControllerActionUrlFromCurrentRouteParams($controllerAction));
    }

    public function getControllerActionParamsFromCurrentRouteParams(ReflectionMethod $controllerAction)
    {
        return $this->resolveMethodDependencies($this->getCurrentRouteParams(false), $controllerAction);
    }

    protected function getCurrentRouteParams(bool $returnOriginalParams): array
    {
        return $returnOriginalParams
            ? $this->getRouter()->getCurrentRoute()->originalParameters()
            : $this->getRouter()->getCurrentRoute()->parameters();
    }

    protected function getRouter(): Router
    {
        return $this->router;
    }

    protected function getParametersForRouteFromCurrentRouteParams(string $routeName): array
    {
        $params = $this->getCurrentRouteParams(true);
        $route = $this->getRouter()->getRoutes()->getByName($routeName);

        if (!$route) {
            return [];
        }

        $routeParamsNames = $route->parameterNames();

        return \array_filter($params, fn($paramName) => \in_array($paramName, $routeParamsNames), \ARRAY_FILTER_USE_KEY);
    }

    protected function getParametersForControllerActionUrlFromCurrentRouteParams(string $controllerAction): array
    {
        $params = $this->getCurrentRouteParams(true);
        $route = $this->getRouter()->getRoutes()->getByAction($controllerAction);

        if (!$route) {
            return [];
        }

        $routeParamsNames = $route->parameterNames();

        return \array_filter($params, fn($paramName) => \in_array($paramName, $routeParamsNames), \ARRAY_FILTER_USE_KEY);
    }
}
