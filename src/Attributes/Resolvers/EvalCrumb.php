<?php

namespace ErickComp\BreadcrumbAttributes\Attributes\Resolvers;

class EvalCrumb implements CrumbResolver
{
    use ResolvesUrlToNull;
    public function __construct(
        public string|\Stringable $code,
        public string|\Stringable|null $routeName = null
    ) {
    }

    public function resolveLabel(array $actionParams): string
    {
        // $evalFn = fn(): string => extract($actionParams) && eval((string) $this->code);

        // return $evalFn();
        extract($actionParams);
        return eval($this->code);
    }
}