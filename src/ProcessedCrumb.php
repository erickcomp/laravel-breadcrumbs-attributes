<?php

namespace ErickComp\BreadcrumbAttributes;

use ErickComp\BreadcrumbAttributes\Attributes\Breadcrumb as BreadcrumbAttribute;
use ErickComp\BreadcrumbAttributes\Util\LazyReflectionMethod;

class ProcessedCrumb
{
    public function __construct(
        public readonly string $label,
        public readonly ?string $url = null
    ) {

    }
}