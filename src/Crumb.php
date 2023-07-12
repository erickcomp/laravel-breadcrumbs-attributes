<?php

namespace ErickComp\BreadcrumbAttributes;

use ErickComp\BreadcrumbAttributes\Attributes\Breadcrumb as BreadcrumbAttribute;
use ErickComp\BreadcrumbAttributes\Util\LazyReflectionMethod;

class Crumb
{
    public function __construct(
        public readonly BreadcrumbAttribute $crumbData,
        public readonly LazyReflectionMethod $reflControllerAction
    ) {
    }

    public function getControllerAction(): string
    {
        $controllerAction = $this->reflControllerAction->class . '@' . $this->reflControllerAction->method;

        if (\str_starts_with($controllerAction, '\\')) {
            $controllerAction = \substr($controllerAction, 1);
        }

        return $controllerAction;
    }
}
