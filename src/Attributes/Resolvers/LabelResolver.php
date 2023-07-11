<?php

namespace ErickComp\BreadcrumbAttributes\Attributes\Resolvers;

interface LabelResolver
{
    public function resolveLabel(array $actionParams): string;
}