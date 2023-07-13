<?php

namespace ErickComp\BreadcrumbAttributes\Attributes\Resolvers;

class ActionParam implements CrumbResolver
{
    use ResolvesUrlToNull;
    public function __construct(
        public readonly string|\Stringable $paramName,
        public readonly string|\Stringable $routeName = '',
    ) {
    }

    public function resolveLabel(array $actionParams): string
    {
        return $actionParams[$this->paramName];
    }

    public function resolveUrl(array $actionParams): ?string
    {
        return null;
    }


}
