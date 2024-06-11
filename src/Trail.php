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

    /** @return ProcessedCrumb[] */
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
        /** @var Crumb[] */
        $crumbsAttrs = $this->crumbBasket->getCrumbsAttributesTrailForRoute($this->router->getCurrentRoute());

        foreach ($crumbsAttrs as $crumbAttr) {
            $this->addCrumbToTrail($crumbAttr);
        }
    }

    protected function addCrumbToTrail(Crumb $crumbItem)
    {
        $urlParams = $this->router->getCurrentRoute()->parameters();
        $controllerActionReflectionMethod = $crumbItem->reflControllerAction->get();

        $controllerActionParamsWithUrlParamsNames = $this->controllerActionRoutesAndParamsResolver->resolveMethodDependencies(
            $urlParams,
            $controllerActionReflectionMethod
        );

        $controllerActionParams = [];

        foreach ($controllerActionReflectionMethod->getParameters() as $controllerActionParam) {
            $controllerActionParams[$controllerActionParam->getName()] = \array_shift($controllerActionParamsWithUrlParamsNames);
        }

        $breadcrumbAttr = $crumbItem->crumbData;

        $this->addAuxCrumbToTrail($breadcrumbAttr->auxCrumbBefore, $controllerActionParams);

        // alterar aqui para somente permitir 2 possibilidades:
        // 1 - string: que pode ser:
        //   1.1 - String comum
        //   1.2 - String com parametro: {parametro} 
        //   1.3 - String usando property de um parametro: {parametro}->propName
        //   1.4 - String usando metodo de um parametro com a seguinte sintaxe:
        //         {requestParam}->method2();
        //         {requestParam}->method(int:1, bool:true, null:null, float:3.14, string:paramN, {otherRequestParam})
        // 2 - Um array, onde cada posição é uma string que será avaliada de acordo com o item 1.
        //     Estas strings serão concatenadas para formar o breadcrumb.
        $crumbLabel = $breadcrumbAttr->label instanceof LabelResolver
            ? $this->resolveLabelResolver($breadcrumbAttr->label, $controllerActionParams)
            : (string) $breadcrumbAttr->label;

        $crumbUrl = $this->controllerActionRoutesAndParamsResolver->resolveControllerActionUrlWithCurrentRouteParams([
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
