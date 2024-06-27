<?php

namespace ErickComp\BreadcrumbAttributes\Util;

interface LazyReflectionMethodInterface
{
    public function isInitialized(): bool;

    public function get(): \ReflectionMethod;
}
