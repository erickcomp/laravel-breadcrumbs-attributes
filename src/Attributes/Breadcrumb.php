<?php

namespace ErickComp\BreadcrumbAttributes\Attributes;

use Attribute;
use ErickComp\BreadcrumbAttributes\Attributes\Resolvers\LabelResolver;

#[Attribute(Attribute::TARGET_METHOD)]
class Breadcrumb
{
    public function __construct(
        public string|\Stringable|array $label,
        public string|\Stringable|null $parent = null,
        public string|\Stringable|null $name = null,
        public string|\Stringable|array|null $before = null,
        public string|\Stringable|array|null $after = null,
    ) {}
}
