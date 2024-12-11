<?php

namespace ErickComp\BreadcrumbAttributes\BladeComponents;

use ErickComp\BreadcrumbAttributes\Trail;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\Factory as ViewFactory;
use ErickComp\BreadcrumbAttributes\Enums\ConfigWhenNoCrumbFound;

class Breadcrumbs extends Component
{
    public function __construct(
        protected Trail $breadcrumbsTrail,
        protected ViewFactory $viewFactory,
        public string $class = '',
        public string $activeClass = 'active',
    ) {}

    public function render()
    {
        try {
            $crumbs = $this->breadcrumbsTrail->getCrumbs();
        } catch (\LogicException $e) {
            $noCrumbErrMsg = 'Error building breadcrumb trail: Could not find crumb for the controller action ';

            if (\str_starts_with($e->getMessage(), $noCrumbErrMsg)) {
                /** @var ConfigWhenNoCrumbFound $configWhenAlreadyDefined*/
                $configWhenAlreadyDefined = config('erickcomp-laravel-breadcrumbs-attributes.when_no_crumb_found');

                if ($configWhenAlreadyDefined === ConfigWhenNoCrumbFound::ThrowException) {
                    throw $e;
                }

                $crumbs = [];
            } else {
                throw $e;
            }
        }

        $viewFile = Str::replaceEnd('.php', '.blade.php', __FILE__);
        return $this->viewFactory->file($viewFile, ['crumbs' => $crumbs]);
    }
}
