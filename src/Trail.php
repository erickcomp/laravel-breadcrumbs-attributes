<?php

namespace ErickComp\BreadcrumbAttributes;

use ErickComp\BreadcrumbAttributes\Attributes\Resolvers\CrumbResolver;
use ErickComp\BreadcrumbAttributes\Attributes\Resolvers\LabelResolver;
use ErickComp\BreadcrumbAttributes\Util\ControllerActionRoutesAndParamsResolver;
use Illuminate\Routing\Router;

class Trail
{
    /** @var ProcessedCrumb[] */
    private array $crumbsTrail;

    public function __construct(
        protected Router $router,
        protected CrumbBasket $crumbBasket,
        protected ControllerActionRoutesAndParamsResolver $controllerActionRoutesAndParamsResolver
    ) {
    }

    public function getCrumbBasket(): CrumbBasket
    {
        return $this->crumbBasket;
    }

    /** @return Crumb[] */
    public function getCrumbs(bool $forceRebuild = false): array
    {
        if (!isset($this->crumbsTrail) || $forceRebuild) {
            $this->build();
        }

        return $this->crumbsTrail;
    }

    protected function build()
    {
        $this->crumbsTrail = [];
        // $routeName = $this->router->getCurrentRoute()->getName();

        // if (!$routeName) {
        //     throw new \LogicException("Cannot generate breadcrumb for an unnamed route (uri: {$this->router->getCurrentRoute()->uri()}).");
        // }

        /** @var Crumb[] */
        $crumbsAttrs = $this->crumbBasket->getCrumbsAttributesTrailForRoute($this->router->getCurrentRoute());

        foreach ($crumbsAttrs as $crumbAttr) {
            $this->addCrumbToTrail($crumbAttr);
        }
    }

    protected function addCrumbToTrail(Crumb $crumbItem)
    {
        $urlParams = $this->router->getCurrentRoute()->parameters();
        $controllerActionParams = $this->controllerActionRoutesAndParamsResolver->resolveMethodDependencies(
            $urlParams,
            $crumbItem->reflControllerAction->get()
        );

        $breadcrumbAttr = $crumbItem->crumbData;

        $this->addAuxCrumbToTrail($breadcrumbAttr->auxCrumbBefore, $controllerActionParams);



        $crumbLabel = $breadcrumbAttr->label instanceof LabelResolver
            ? $this->resolveLabelResolver($breadcrumbAttr->label, $controllerActionParams)
            : (string) $breadcrumbAttr->label;

        $crumbUrl = $breadcrumbAttr->routeName
            ? $this->controllerActionRoutesAndParamsResolver->resolveRouteUrlWithCurrentRouteParams($breadcrumbAttr->routeName)
            : $this->controllerActionRoutesAndParamsResolver->resolveControllerActionUrlWithCurrentRouteParams([
                $crumbItem->reflControllerAction->class,
                $crumbItem->reflControllerAction->method
            ]);

        $this->crumbsTrail[] = new ProcessedCrumb(
            $crumbLabel,
            $crumbUrl
        );

        $this->addAuxCrumbToTrail($breadcrumbAttr->auxCrumbAfter, $controllerActionParams);
    }

    protected function resolveLabelResolver(LabelResolver $resolver, array $controllerActionParams): string
    {
        return $resolver->resolveLabel($controllerActionParams);
    }

    protected function resolverUrl(string $routeName, array $urlParams): ?string
    {

    }

    public function addAuxCrumbToTrail(LabelResolver|CrumbResolver|\Stringable|string|null $auxCrumb, array $controllerActionParams)
    {
        if ($auxCrumb instanceof CrumbResolver) {
            $this->crumbsTrail[] = $this->resolveCrumbResolver($auxCrumb, $controllerActionParams);
        } elseif ($auxCrumb instanceof LabelResolver) {
            $this->crumbsTrail[] = new ProcessedCrumb($this->resolveLabelResolver($auxCrumb, $controllerActionParams));
        } elseif ($auxCrumb !== null) {
            $this->crumbsTrail[] = new ProcessedCrumb((string) $auxCrumb);
        }
    }

    protected function resolveCrumbResolver(
        CrumbResolver $crumbResolver,
        array $controllerActionParams
    ): ProcessedCrumb {
        return new ProcessedCrumb(
            $crumbResolver->resolveLabel($controllerActionParams),
            $crumbResolver->resolveUrl($controllerActionParams)
        );
    }
}