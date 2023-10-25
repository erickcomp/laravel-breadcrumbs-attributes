<?php

namespace ErickComp\BreadcrumbAttributes\Facades;

use ErickComp\BreadcrumbAttributes\Trail;
use Illuminate\Support\Facades\Facade;

use ErickComp\BreadcrumbAttributes\CrumbBasket;
use ErickComp\BreadcrumbAttributes\ProcessedCrumb;

/**
 * class BreadcrumbsTrail
 * 
 * @method public static CrumbBasket getCrumbBasket()
 * @method public static ProcessedCrumb[] getCrumbs(bool $forceRebuild = false)
 */
class BreadcrumbsTrail extends Facade
{
    /**
     *
     * @inheritDoc
     */
    protected static function getFacadeAccessor()
    {
        return Trail::class;
    }
}
