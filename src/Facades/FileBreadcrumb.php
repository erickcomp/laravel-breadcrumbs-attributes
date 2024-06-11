<?php

namespace ErickComp\BreadcrumbAttributes\Facades;

use ErickComp\BreadcrumbAttributes\FileBreadcrumb as FileBreadcrumbClass;
use Illuminate\Support\Facades\Facade;

use ErickComp\BreadcrumbAttributes\CrumbBasket;
use ErickComp\BreadcrumbAttributes\ProcessedCrumb;

/**
 * class FileBreadcrumb
 * 
 * @method public static CrumbBasket putCrumb()
 * @method public static ProcessedCrumb[] getCrumbs(bool $forceRebuild = false)
 */
class FileBreadcrumb extends Facade
{
    /**
     *
     * @inheritDoc
     */
    protected static function getFacadeAccessor()
    {
        return FileBreadcrumbClass::class;
    }
}
