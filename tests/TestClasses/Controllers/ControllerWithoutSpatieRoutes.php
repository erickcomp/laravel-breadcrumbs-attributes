<?php

namespace ErickComp\BreadcrumbAttributes\Tests\TestClasses\Controllers;

use ErickComp\BreadcrumbAttributes\Attributes\Breadcrumb;
use ErickComp\BreadcrumbAttributes\Attributes\Resolvers\EvalCrumb;
use ErickComp\BreadcrumbAttributes\Tests\TestClasses\Models\FakeModel;

class ControllerWithoutSpatieRoutes
{
    #[Breadcrumb('/', name: 'home')]
    public function home()
    {
    }

    #[Breadcrumb('Simple', parent: 'home', name: 'home.aSimple')]
    public function aSimple()
    {
    }

    #[Breadcrumb('ActionParam', parent: 'home', name: 'home.aActionParam')]
    public function aActionParam()
    {
    }

    #[Breadcrumb('ActionParamMethod', parent: 'home', name: 'home.aActionParamMethod')]
    public function aActionParamMethod()
    {
    }

    #[Breadcrumb('ActionParamProperty', parent: 'home', name: 'home.aActionParamProperty')]
    public function aActionParamProperty()
    {
    }

    #[Breadcrumb('ConcatLabel', parent: 'home', name: 'home.aConcatLabel')]
    public function aConcatLabel()
    {
    }

    #[Breadcrumb(
        label: new EvalCrumb('return $param1 . ": " . \strtoupper($param2->name);'),
        parent: 'home',
        name: 'home.aEvalCrumb'
    )]
    public function aEvalCrumb(string $param1, FakeModel $fakeModel)
    {
    }
}
