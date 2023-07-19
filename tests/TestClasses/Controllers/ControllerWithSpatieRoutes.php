<?php

namespace ErickComp\BreadcrumbAttributes\Tests\TestClasses\Controllers;

use ErickComp\BreadcrumbAttributes\Attributes\Breadcrumb;
use ErickComp\BreadcrumbAttributes\Facades\BreadcrumbsTrail;
use ErickComp\BreadcrumbAttributes\Tests\TestClasses\Models\FakeModel;
use ErickComp\BreadcrumbAttributes\Tests\TestClasses\SendsBreadcrumbsAsJsonResponse;
use Illuminate\Http\JsonResponse;
use Spatie\RouteAttributes\Attributes\Get;

class ControllerWithSpatieRoutes
{
    use SendsBreadcrumbsAsJsonResponse;

    #[Get('spatie-get-method-simple-no-name', name: 'home.spatie-get-method-no-name')]
    #[Breadcrumb('Spatie Breadcrumb: Simple | No Name')]
    public function spatieGetMethodSimpleNoName()
    {
        $this->sendsBreadcrumbsAsJsonResponse();
    }

    #[Get('spatie-get-method-simple-named', name: 'home.spatie-get-method-named')]
    #[Breadcrumb('Spatie Breadcrumb: Simple | Named', parent: 'home', name: 'spatie-get-simple-named')]
    public function spatieGetMethodSimpleNamed()
    {
        $this->sendsBreadcrumbsAsJsonResponse();
    }
}
