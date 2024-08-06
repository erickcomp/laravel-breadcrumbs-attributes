<?php

use ErickComp\BreadcrumbAttributes\Providers\BreadcrumbsAttributeServiceProvider;
use ErickComp\BreadcrumbAttributes\Enums\ConfigWhenAlreadyDefined;

return [
    'controller_directories' => [
        app_path('Http/Controllers')
    ],
    'breadcrumbs_files' => [
        BreadcrumbsAttributeServiceProvider::defaultAttributelessBreadcrumbsFile()
    ],
    'when_already_defined' => ConfigWhenAlreadyDefined::ThrowException
];
