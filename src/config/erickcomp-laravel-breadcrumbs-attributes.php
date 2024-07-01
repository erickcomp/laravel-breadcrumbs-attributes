<?php

use ErickComp\BreadcrumbAttributes\Providers\BreadcrumbsAttributeServiceProvider;

return [
    'controller_directories' => [
        app_path('Http/Controllers')
    ],
    'breadcrumbs_files' => [
        BreadcrumbsAttributeServiceProvider::defaultAttributelessBreadcrumbsFile()
    ]
];
