<?php

namespace ErickComp\BreadcrumbAttributes\Facades;

use ErickComp\BreadcrumbAttributes\CrumbBasket as CrumbBasketAccessor;
use Illuminate\Support\Facades\Facade;

/**
 * class CrumbBasket
 * 
 * @method void static gatherCrumbsOntoBasket(bool $ignoreCache = false)
 * @method void static putCrumbForRouteName(string|\Stringable $routeName, string|\Stringable $label, string|\Stringable|null $parent = null, string|\Stringable|null $name = null, string|\Stringable|array|null $auxCrumbBefore = null, string|\Stringable|null $auxCrumbAfter = null)
 * @method void static putCrumbForControllerAction(string|array $controllerAction, string|\Stringable $label, string|\Stringable|null $name, string|\Stringable|null $parent = null, string|\Stringable|array|null $auxCrumbBefore = null, string|\Stringable|null $auxCrumbAfter = null)
 * @method Crumb[] static getCrumbsAttributesTrailForRoute(Route $route)
 * @method bool static breadcrumbsAreCached()
 * @method void static cacheBreadcrumbs();
 * @method void static clearBreadcrumbsCache();
 */
class CrumbBasket extends Facade
{
    /**
     *
     * @inheritDoc
     */
    protected static function getFacadeAccessor()
    {
        return CrumbBasketAccessor::class;
    }
}
