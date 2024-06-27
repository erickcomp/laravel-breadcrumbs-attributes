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

        // $crumbLabel = $breadcrumbAttr->label instanceof LabelResolver
        //     ? $this->resolveLabelResolver($breadcrumbAttr->label, $controllerActionParams)
        //     : (string) $breadcrumbAttr->label;
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

        $crumbLabel = $this->resolveLabel($breadcrumbAttr->label, $controllerActionParams);

        $crumbUrl = $this->controllerActionRoutesAndParamsResolver->resolveControllerActionUrlWithCurrentRouteParams([
            $crumbItem->reflControllerAction->getClass(),
            $crumbItem->reflControllerAction->getMethod()
        ]);

        $this->crumbsTrail[] = new ProcessedCrumb(
            $crumbLabel,
            $crumbUrl
        );

        $this->addAuxCrumbToTrail($breadcrumbAttr->auxCrumbAfter, $controllerActionParams);
    }

    /**
     * @param string|\Stringable|array<string|\Stringable> $label
     */
    protected function resolveLabel(string|\Stringable|array $label, array $controllerActionParams): string
    {
        if ($label instanceof \Stringable) {
            $label = (string) $label;
        }

        // Treating common case of ' ' as separator for concat breadcrumbs
        if ($label === ' ') {
            return ' ';
        }

        if (\is_array($label)) {
            return \implode('', \array_map(fn($labelItem) => $this->resolveLabel($labelItem, $controllerActionParams), $label));
        }

        $label = trim($label);

        if (\str_starts_with($label, '@php:')) {
            return $this->parsePhpLabel($label, $controllerActionParams);
        }

        // No request params on this label, it's just a simple string.
        // Let's return it right away
        if (!\str_contains($label, '{') && !\str_contains($label, '}')) {
            return $label;
        }

        // Checking if it's a request param label
        if (\str_starts_with($label, '{') && \str_ends_with($label, '}')) {
            return $this->parseRequestParamLabel($label, $controllerActionParams);
        }

        // Checking if it's a method call on a request param
        preg_match('/^\{(\w+)\}->(\w+)\((.*?)\)$/', $label, $matches);

        if (!empty($matches)) {
            return $this->parseRequestParamMethodLabel($matches[1], $matches[2], $matches[3], $controllerActionParams);
        }

        // Checking if it's accessing a property of request param
        preg_match('/^\{(\w+)\}->(\w+)$/', $label, $matches);

        if (!empty($matches)) {
            return $this->parseRequestParamPropertyLabel($matches[1], $matches[2], $controllerActionParams);
        }

        // Return a plain string. It should not get here, to be honest...
        return $label;
    }

    protected function parsePhpLabel(string $label, array $controllerActionParams): string
    {
        extract($controllerActionParams);
        $phpCode = 'return ' . \substr($label, 5) . ';';

        return eval ($phpCode);
    }
    protected function parseRequestParamLabel(string $label, array $controllerActionParams): string
    {
        $requestParamName = trim($label, '{}');

        // check if there's such param for the action
        $this->assertParamExists($requestParamName, $controllerActionParams);

        return (string) $controllerActionParams[$requestParamName];
    }

    protected function parseRequestParamMethodLabel(
        string $requestParamName,
        string $requestParamMethod,
        string $argsString,
        array $controllerActionParams
    ): string {
        $args = explode(',', $argsString);
        $breadcrumb['args'] = [];
        $parsedArgs = [];

        // check if there's such param for the action
        $this->assertParamExists($requestParamName, $controllerActionParams);

        // parse method args
        foreach ($args as $argNum => $arg) {
            $arg = trim($arg);

            if (preg_match('/^{(\w+)}$/', $arg, $argMatches)) {
                // Checking if it's using another request param as arg for the method call

                //$breadcrumb['args'][] = ['type' => 'requestParam', 'value' => $argMatches[1]];
                $parsedArgs[] = $controllerActionParams[$argMatches[1]];
            } elseif (preg_match('/^(\w+):(.+)$/', $arg, $argMatches)) {
                // Checking for typed scalars args on the method call

                $type = $argMatches[1];
                //$value = filter_var($argMatches[2], constant("FILTER_VALIDATE_$type"));
                $parsedArgs[] = static::filterLabelMethodCallArgument($argMatches[2], $argMatches[1], $argNum + 1);
            } else {
                // Will use the arg as string
                $parsedArgs[] = $arg;
            }
        }

        // call the method on the request param
        $requestParamObj = $controllerActionParams[$requestParamName];
        return $requestParamObj->{$requestParamMethod}(...$parsedArgs);
    }

    protected function parseRequestParamPropertyLabel(
        string $requestParamName,
        string $requestParamProperty,
        array $controllerActionParams
    ): string {

        // check if there's such param for the action
        $this->assertParamExists($requestParamName, $controllerActionParams);

        $requestParamObj = $controllerActionParams[$requestParamName];

        return $requestParamObj->{$requestParamProperty};
    }

    protected function assertParamExists(string $requestParamName, array $controllerActionParams)
    {
        if (!\array_key_exists($requestParamName, $controllerActionParams)) {
            $errmsg = "Error creating breadcrumb: There's no parameter named [$requestParamName] for this controller action";

            throw new \LogicException($errmsg);
        }
    }

    protected static function filterLabelMethodCallArgument($value, string $type, int $argNum)
    {
        static $parseErrorMessageBuilder = null;
        if ($parseErrorMessageBuilder === null) {
            $parseErrorMessageBuilder = static function ($given, $typeExpected, $typeGiven, $argNum) {
                $errmsg = "Argument #$argNum must be of type $typeExpected, $typeGiven given";

                return $errmsg;
            };
        }

        if (\is_null($type)) {
            if (\is_null($value)) {
                return null;
            }

            $errMsg = $parseErrorMessageBuilder($value, 'null', 'string', $argNum);

            throw new \TypeError($errMsg);
        }

        $filter = match (\strtolower($type)) {
            'int' => FILTER_VALIDATE_INT,
            'integer' => FILTER_VALIDATE_INT,
            'float' => FILTER_VALIDATE_FLOAT,
            'double' => FILTER_VALIDATE_FLOAT,
            'bool' => FILTER_VALIDATE_BOOL,
            'boolean' => FILTER_VALIDATE_BOOL,
            default => FILTER_DEFAULT
        };

        $parsed = \filter_var($value, $filter, FILTER_NULL_ON_FAILURE);

        if ($parsed === null) {
            $errMsg = $parseErrorMessageBuilder($value, $type, 'string', $argNum);

            throw new \TypeError($errMsg);
        }

        return $parsed;
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
