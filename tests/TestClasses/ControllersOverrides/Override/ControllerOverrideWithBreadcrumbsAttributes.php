<?php

namespace ErickComp\BreadcrumbAttributes\Tests\TestClasses\ControllersOverrides\Override;

use ErickComp\BreadcrumbAttributes\Attributes\Breadcrumb;
use ErickComp\BreadcrumbAttributes\Tests\TestClasses\ControllersOverrides\Base\ControllerBaseWithBreadcrumbsAttributes;
use Spatie\RouteAttributes\Attributes\Get;

class ControllerOverrideWithBreadcrumbsAttributes extends ControllerBaseWithBreadcrumbsAttributes
{
    #[Get('base-and-overrides-controllers', name: 'base-and-overrides-controllers')]
    #[Breadcrumb('Breadcrumb from CHILD_CONTROLLER')]
    public function test()
    {
        return $this->sendsBreadcrumbsAsJsonResponse();
    }
}
