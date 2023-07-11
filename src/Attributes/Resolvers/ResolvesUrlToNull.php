<?php

namespace ErickComp\BreadcrumbAttributes\Attributes\Resolvers;

trait ResolvesUrlToNull
{
    public function resolveUrl(array $actionParams): ?string
    {
        return null;
    }
}