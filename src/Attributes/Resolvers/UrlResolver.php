<?php

namespace ErickComp\BreadcrumbAttributes\Attributes\Resolvers;

interface UrlResolver
{
    public function resolveUrl(array $actionParams): ?string;
}