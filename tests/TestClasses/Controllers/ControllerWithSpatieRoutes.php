<?php

namespace ErickComp\BreadcrumbAttributes\Tests\TestClasses\Controllers;

use Spatie\RouteAttributes\Attributes\Get;

class AnyTestController
{
    #[Get('spatie-get-method')]
    public function spatieGetMethod()
    {
    }
}
