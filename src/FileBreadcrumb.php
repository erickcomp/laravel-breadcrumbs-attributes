<?php

namespace ErickComp\BreadcrumbAttributes;

use ErickComp\BreadcrumbAttributes\Util\LazyReflectionMethod;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Config;
use SplFileInfo;
use ReflectionClass;
use ReflectionMethod;
use ReflectionAttribute;

use Illuminate\Support\Arr;
use Symfony\Component\Finder\Finder;

use ErickComp\BreadcrumbAttributes\Attributes\Breadcrumb;

class FileBreadcrumb
{
    /** @var array<string, Crumb> $crumbs */
    protected array $crumbs = [];

    public function putCrumb(string|array $controllerAction, ...$breadcrumbAttributes)
    {
        [$crumbName, $crumb] = $this->buildCrumb(...\func_get_args());
        $this->crumbs[$crumbName] = $crumb;
    }

    /** @return array<string, Crumb> */
    public function getCrumbs(): array
    {
        return $this->crumbs;
    }

    private function buildCrumb(...$args): Crumb
    {

    }
}
