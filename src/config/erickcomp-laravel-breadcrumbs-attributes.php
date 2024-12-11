<?php

use ErickComp\BreadcrumbAttributes\Enums\ConfigWhenAlreadyDefined;
use ErickComp\BreadcrumbAttributes\Providers\BreadcrumbsAttributeServiceProvider;
use ErickComp\BreadcrumbAttributes\Enums\ConfigWhenNoCrumbFound;

return [
    'controller_directories' => [
        app_path('Http/Controllers'),
    ],
    'breadcrumbs_files' => [
        BreadcrumbsAttributeServiceProvider::defaultAttributelessBreadcrumbsFile(),
    ],
    'when_already_defined' => ConfigWhenAlreadyDefined::ThrowException,
    'inherit_breadcrumb_definition_from_parent_method' => false,
    'when_no_crumb_found' =>ConfigWhenNoCrumbFound::ThrowException,
];
