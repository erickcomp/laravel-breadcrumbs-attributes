<?php

namespace ErickComp\BreadcrumbAttributes\Attributes\Resolvers;

class ActionParamMethod implements CrumbResolver
{
    use ResolvesUrlToNull;

    public function __construct(
        public string|\Stringable $paramName,
        public string|\Stringable $paramMethodName,
        public array $paramMethodPositionalArgs = [],
        public array $paramMethodNamedArgs = [],
        public string|\Stringable|null $routeName = null
    ) {
        if (\str_starts_with($paramName, '$')) {
            $this->paramName = \substr($paramName, 1);
        }

        if (!empty($paramMethodPositionalArgs) && !empty($paramMethodNamedArgs)) {
            throw new \LogicException("You must use either positional or named args, but not both at the same time");
        }
    }

    public function resolveLabel(array $actionParams): string
    {
        $param = $actionParams[$this->paramName];
        $method = $this->paramMethodName;
        $args = $this->paramMethodPositionalArgs ?? $this->paramMethodNamedArgs;

        return $param->$method(...$args);
    }
}
