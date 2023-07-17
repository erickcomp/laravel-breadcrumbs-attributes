<?php

namespace ErickComp\BreadcrumbAttributes\Attributes;

use Attribute;
use ErickComp\BreadcrumbAttributes\Attributes\Resolvers\LabelResolver;

#[Attribute(Attribute::TARGET_METHOD)]
class Breadcrumb
{
    public function __construct(
        public string|\Stringable|LabelResolver $label,
        public string|\Stringable|null $parent = null,
        public string|\Stringable|null $name = null,
        public string|\Stringable|LabelResolver|null $auxCrumbBefore = null,
        public string|\Stringable|LabelResolver|null $auxCrumbAfter = null //,
        //public string|\Stringable|null $routeName = null
    ) {
    }
}
