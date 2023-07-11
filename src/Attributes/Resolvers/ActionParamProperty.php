<?php

namespace ErickComp\BreadcrumbAttributes\Attributes\Resolvers;

class ActionParamProperty implements CrumbResolver
{
    use ResolvesUrlToNull;

    public function __construct(
        public string|\Stringable $paramName,
        public string|\Stringable $paramPropertyName,
        public string|\Stringable|null $routeName = null
    ) {
    }

    public function resolveLabel(array $actionParams): string
    {
        $param = $actionParams[$this->paramName];
        $property = $this->paramPropertyName;

        return $param->$property;
    }
}