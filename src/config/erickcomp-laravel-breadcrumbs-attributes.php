<?php

use ErickComp\BreadcrumbAttributes\Enums\ConfigWhenAlreadyDefined;
use ErickComp\BreadcrumbAttributes\Providers\BreadcrumbsAttributeServiceProvider;

return [
    'controller_directories' => [
        app_path('Http/Controllers')
    ],
    'breadcrumbs_files' => [
        BreadcrumbsAttributeServiceProvider::defaultAttributelessBreadcrumbsFile()
    ],
    'when_already_defined' => ConfigWhenAlreadyDefined::ThrowException,
    'inherit_breadcrumb_definition_from_parent_method' => false
];
