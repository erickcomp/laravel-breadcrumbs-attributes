<?php

namespace ErickComp\BreadcrumbAttributes\Facades;

use ErickComp\BreadcrumbAttributes\Trail;
use Illuminate\Support\Facades\Facade;

use ErickComp\BreadcrumbAttributes\CrumbBasket;

/**
 * class BreadcrumbsTrail
 * 
 * @method public static CrumbBasket getCrumbBasket()
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