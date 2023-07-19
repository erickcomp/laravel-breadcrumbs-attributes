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
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

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

    #[Breadcrumb(new ActionParam('param1'), parent: 'home', name: 'home.aActionParam')]
    public function aActionParam(string $param1)
    {
        return $this->sendsBreadcrumbsAsJsonResponse();
    }

    #[Breadcrumb(new ActionParamMethod('request', 'fullUrl'), parent: 'home', name: 'home.aActionParamMethod')]
    public function aActionParamMethod(Request $request)
    {
        return $this->sendsBreadcrumbsAsJsonResponse();
    }

    #[Breadcrumb(new ActionParamProperty('route', 'uri'), parent: 'home', name: 'home.aActionParamProperty')]
    public function aActionParamProperty(Route $route)
    {
        return $this->sendsBreadcrumbsAsJsonResponse();
    }

    #[Breadcrumb(
        label: new ConcatLabel('Concat part 1', '|', 'Concat part 2'),
        parent: 'home',
        name: 'home.aConcatLabel'
    )]
    public function aConcatLabel()
    {
        return $this->sendsBreadcrumbsAsJsonResponse();
    }

    #[Breadcrumb(
        label: new EvalCrumb('return $param1 . ": " . \strtoupper($fakeModel->name);'),
        parent: 'home',
        name: 'home.aEvalCrumb'
    )]
    public function aEvalCrumb(string $param1, FakeModel $fakeModel)
    //public function aEvalCrumb(string $param1, string $fakeModel)
    {
        return $this->sendsBreadcrumbsAsJsonResponse();
    }
}
