<?php

namespace ErickComp\BreadcrumbAttributes;

class ProcessedCrumb
{
    public function __construct(
        public readonly string $label,
        public readonly ?string $url = null,
    ) {}
}
