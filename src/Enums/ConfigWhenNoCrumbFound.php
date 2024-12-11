<?php

namespace ErickComp\BreadcrumbAttributes\Enums;

enum ConfigWhenNoCrumbFound: string
{
    case ThrowException = 'throw_exception';
    case RenderEmpty = 'render_empty';
}
