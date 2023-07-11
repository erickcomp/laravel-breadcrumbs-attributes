<?php

namespace ErickComp\BreadcrumbAttributes\Attributes\Resolvers;

class ConcatLabel implements LabelResolver
{
    private string|\Stringable $separator;

    private string|\Stringable|null $routeName;

    /** @var string|\Stringable|CrumbResolver[] $crumbs */
    private array $crumbs = [];

    public function __construct(string|\Stringable|CrumbResolver...$crumbs)
    {
        $this->crumbs = $crumbs;
    }

    public function resolveLabel(array $actionParams): string
    {
        return \implode(
            '',
            \array_map(
                fn($crumb) => $this->resolveCrumbLabel($crumb, $actionParams),
                $this->crumbs
            )
        );
    }

    private function resolveCrumbLabel(string|\Stringable|LabelResolver $crumb, array $actionParams): string
    {
        if (\is_string($crumb)) {
            return $crumb;
        }

        if ($crumb instanceof \Stringable) {
            return (string) $crumb;
        }

        return $crumb->resolveLabel($actionParams);
    }
}