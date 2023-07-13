<?php

namespace ErickComp\BreadcrumbAttributes\BladeComponents;

use Illuminate\View\Component;
use Illuminate\Support\Str;
use Illuminate\View\Factory as ViewFactory;
use ErickComp\BreadcrumbAttributes\Trail;

class Breadcrumbs extends Component
{
    public function __construct(
        protected Trail $breadcrumbsTrail,
        protected ViewFactory $viewFactory,
        public string $class = '',
        public string $activeClass = 'active'
    ) {
    }

    public function render()
    {
        $viewFile = Str::replaceLast('.php', '.blade.php', __FILE__);
        $crumbs = $this->breadcrumbsTrail->getCrumbs();

        return $this->viewFactory->file($viewFile, ['crumbs' => $crumbs]);
    }
}
