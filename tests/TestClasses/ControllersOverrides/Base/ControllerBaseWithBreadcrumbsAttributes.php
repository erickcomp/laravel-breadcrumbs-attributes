<?php

namespace ErickComp\BreadcrumbAttributes\Tests\TestClasses\ControllersOverrides\Base;

use ErickComp\BreadcrumbAttributes\Attributes\Breadcrumb;
use ErickComp\BreadcrumbAttributes\Tests\TestClasses\SendsBreadcrumbsAsJsonResponse;
use Spatie\RouteAttributes\Attributes\Get;

class ControllerBaseWithBreadcrumbsAttributes
{
    use SendsBreadcrumbsAsJsonResponse;

    #[Get('base-and-overrides-controllers', name: 'base-and-overrides-controllers')]
    #[Breadcrumb('Breadcrumb from BASE_CONTROLLER')]
    public function test()
    {
        return $this->sendsBreadcrumbsAsJsonResponse();
    }
}
