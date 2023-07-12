<?php

namespace ErickComp\BreadcrumbAttributes\Commands;

use ErickComp\BreadcrumbAttributes\CrumbBasket;
use Illuminate\Console\Command;

class ClearBreadcrumbsCacheCommand extends Command
{
    public $signature = 'erickcomp:clear-breadcrumbs-cache';

    public $description = 'Deletes the breadcrumbs cache, so the controllers are scanned in every request';

    public function handle(CrumbBasket $crumbBasket)
    {
        $crumbBasket->clearBreadcrumbsCache();

        if ($crumbBasket->breadcrumbsAreCached()) {
            $this->error('Cache could not be cleared');
        } else {
            $this->comment('Cache cleared');
        }
    }
}