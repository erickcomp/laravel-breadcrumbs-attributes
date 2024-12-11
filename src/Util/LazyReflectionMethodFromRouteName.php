<?php

namespace ErickComp\BreadcrumbAttributes\Util;

use Illuminate\Support\Facades\Route;

class LazyReflectionMethodFromRouteName implements LazyReflectionMethodInterface
{
    private ?\ReflectionMethod $reflMethod;

    public function __construct(
        public readonly string $routeName,
    ) {}

    public function getClass(): ?string
    {
        return $this->get()?->getDeclaringClass()?->getName();
    }

    public function getMethod(): ?string
    {
        return $this->get()?->getName();
    }

    public function __serialize()
    {
        return [
            'routeName' => $this->routeName,
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

    public function get(): ?\ReflectionMethod
    {
        if (!$this->isInitialized()) {
            $route = Route::getRoutes()->getByName($this->routeName);

            if (!$route) {
                $errmsg = "Could not find a route with the name [{$this->routeName}]";

                throw new \LogicException($errmsg);
            }

            $controllerClass = $route->getControllerClass();

            $this->reflMethod = $controllerClass === null
                ? null
                : new \ReflectionMethod(
                    $route->getControllerClass(),
                    $route->getActionMethod(),
                );
        }

        return $this->reflMethod;
    }
}
