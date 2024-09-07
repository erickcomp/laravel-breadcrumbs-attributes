<?php

namespace ErickComp\BreadcrumbAttributes\Enums;

enum ConfigWhenAlreadyDefined: string
{
    case ThrowException = 'throw_exception';
    case Ignore = 'ignore';
    case Override = 'override';
}
