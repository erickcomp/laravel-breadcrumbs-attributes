<?php

namespace ErickComp\BreadcrumbAttributes;

use ErickComp\BreadcrumbAttributes\Attributes\Breadcrumb as BreadcrumbAttribute;
use ErickComp\BreadcrumbAttributes\Attributes\Resolvers\LabelResolver;
use ErickComp\BreadcrumbAttributes\Util\LazyReflectionMethodInterface;
use ErickComp\BreadcrumbAttributes\Util\LazyReflectionMethod;
use Illuminate\Support\Arr;

class Crumb
{
    public function __construct(
        public readonly BreadcrumbAttribute $crumbData,
        public readonly LazyReflectionMethodInterface $reflControllerAction
    ) {
    }

    public static function create(
        $controllerAction,
        string|\Stringable|LabelResolver $label,
        string|\Stringable|null $parent = null,
        string|\Stringable|null $name = null,
        string|\Stringable|LabelResolver|null $auxCrumbBefore = null,
        string|\Stringable|LabelResolver|null $auxCrumbAfter = null //,
        //string|\Stringable|null $routeName = null
    ): static {
        $crumbAttribute = new BreadcrumbAttribute($label, $parent, $name, $auxCrumbBefore, $auxCrumbAfter);
        $lazyReflMethod = self::buildLazyReflectionMethod($controllerAction);

        return new static($crumbAttribute, $lazyReflMethod);
    }

    public function getControllerAction(): string
    {
        //$controllerAction = $this->reflControllerAction->class . '@' . $this->reflControllerAction->method;
        $controllerClass = $this->reflControllerAction->get()->getDeclaringClass();
        $controllerMethod = $this->reflControllerAction->get()->getName();
        $controllerAction =  "$controllerClass@$controllerMethod";

        if (\str_starts_with($controllerAction, '\\')) {
            $controllerAction = \substr($controllerAction, 1);
        }

        return $controllerAction;
    }

    private static function buildLazyReflectionMethod($controllerAction): LazyReflectionMethodInterface
    {
        $reflMethod = new \ReflectionMethod(...Arr::wrap($controllerAction));
        return LazyReflectionMethod::fromReflectionMethod($reflMethod);
    }
}
