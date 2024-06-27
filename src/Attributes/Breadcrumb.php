<?php

namespace ErickComp\BreadcrumbAttributes\Attributes;

use Attribute;
use ErickComp\BreadcrumbAttributes\Attributes\Resolvers\LabelResolver;

#[Attribute(Attribute::TARGET_METHOD)]
class Breadcrumb
{
    public function __construct(
        public string|\Stringable $label,
        public string|\Stringable|null $parent = null,
        public string|\Stringable|null $name = null,
        public string|\Stringable|null $auxCrumbBefore = null,
        public string|\Stringable|null $auxCrumbAfter = null
    ) {
    }
}
