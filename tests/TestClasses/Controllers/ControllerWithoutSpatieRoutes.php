<?php

namespace ErickComp\BreadcrumbAttributes\Tests\TestClasses\Controllers;

use ErickComp\BreadcrumbAttributes\Attributes\Breadcrumb;
use ErickComp\BreadcrumbAttributes\Attributes\Resolvers\ActionParam;
use ErickComp\BreadcrumbAttributes\Attributes\Resolvers\ActionParamMethod;
use ErickComp\BreadcrumbAttributes\Attributes\Resolvers\ActionParamProperty;
use ErickComp\BreadcrumbAttributes\Attributes\Resolvers\ConcatLabel;
use ErickComp\BreadcrumbAttributes\Attributes\Resolvers\EvalCrumb;
use ErickComp\BreadcrumbAttributes\Tests\TestClasses\Models\FakeModel;
use ErickComp\BreadcrumbAttributes\Tests\TestClasses\SendsBreadcrumbsAsJsonResponse;
use ErickComp\BreadcrumbAttributes\Tests\TestClasses\Util\StringableImpl;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

class ControllerWithoutSpatieRoutes
{
    use SendsBreadcrumbsAsJsonResponse;

    #[Breadcrumb('Home', name: 'home')]
    public function home()
    {
        return $this->sendsBreadcrumbsAsJsonResponse();
    }

    #[Breadcrumb('Simple', parent: 'home', name: 'home.aSimple')]
    public function aSimple()
    {
        return $this->sendsBreadcrumbsAsJsonResponse();
    }

    #[Breadcrumb('{param1}', parent: 'home', name: 'home.aActionParam')]
    public function aActionParam(string $param1)
    {
        return $this->sendsBreadcrumbsAsJsonResponse();
    }

    #[Breadcrumb('{request}->fullUrl()', parent: 'home', name: 'home.aActionParamMethod')]
    public function aActionParamMethod(Request $request)
    {
        return $this->sendsBreadcrumbsAsJsonResponse();
    }

    #[Breadcrumb('{route}->uri', parent: 'home', name: 'home.aActionParamProperty')]
    public function aActionParamProperty(Route $route)
    {
        return $this->sendsBreadcrumbsAsJsonResponse();
    }

    #[Breadcrumb(
        label: ['Concat part 1', '|', 'Concat part 2', new StringableImpl('|Concat part 3')],
        parent: 'home',
        name: 'home.aConcatLabel'
    )]
    public function aConcatLabel()
    {
        return $this->sendsBreadcrumbsAsJsonResponse();
    }

    #[Breadcrumb('Last Crumb', parent: 'home.aActionParam', name: 'home.aActionParam.aLastCrumb')]
    public function aReturnComponent()
    {
        return Blade::render('<x-erickcomp-breadcrumbs />');
    }
}
