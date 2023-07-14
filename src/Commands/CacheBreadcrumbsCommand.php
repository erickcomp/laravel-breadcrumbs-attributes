<?php

namespace ErickComp\BreadcrumbAttributes\Commands;

use ErickComp\BreadcrumbAttributes\CrumbBasket;
use Illuminate\Console\Command;

class CacheBreadcrumbsCommand extends Command
{
    public $signature = 'erickcomp:laravel-breadcrumbs-attributes:cache';

    public $description = 'Caches breadcrumbs, so the controllers are not scanned on every request. This is the recommended behavior for production';

    public function handle(CrumbBasket $crumbBasket)
    {
        $crumbBasket->cacheBreadcrumbs();

        if ($crumbBasket->breadcrumbsAreCached()) {
            $this->comment('Cache generated');
        } else {
            $this->error('Could not generate breadcrumbs cache');
        }
    }
}
