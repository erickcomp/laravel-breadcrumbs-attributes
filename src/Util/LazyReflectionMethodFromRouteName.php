<?php

namespace ErickComp\BreadcrumbAttributes\Util;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

class LazyReflectionMethodFromRouteName implements LazyReflectionMethodInterface
{
    private \ReflectionMethod $reflMethod;

    public function __construct(
        public readonly string $routeName
    ) {
    }

    public function __serialize()
    {
        return [
            'routeName' => $this->routeName
        ];
    }

    public function __unserialize(array $data)
    {
        $this->__construct($data['routeName']);
    }

    public function isInitialized(): bool
    {
        return isset($this->reflMethod);
    }

    public function get(): \ReflectionMethod
    {
        if (!$this->isInitialized()) {
            $route = Route::getRoutes()->getByName($this->routeName);

            if (!$route) {
                $errmsg = "Could not find a route with the name [{$this->routeName}]";

                throw new \LogicException($errmsg);
            }

            $this->reflMethod = new \ReflectionMethod(
                $route->getControllerClass(),
                $route->getActionMethod()
            );
        }

        return $this->reflMethod;
    }
}
