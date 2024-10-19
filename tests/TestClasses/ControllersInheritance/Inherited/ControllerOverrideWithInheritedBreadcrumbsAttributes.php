<?php

namespace ErickComp\BreadcrumbAttributes\Tests\TestClasses\ControllersInheritance\Inherited;

use ErickComp\BreadcrumbAttributes\Facades\BreadcrumbsTrail;
use ErickComp\BreadcrumbAttributes\Tests\TestClasses\ControllersInheritance\Base\ControllerBaseWithBreadcrumbsAttributesThatWillBeInherited;
use Illuminate\Http\JsonResponse;
use Spatie\RouteAttributes\Attributes\Get;

class ControllerOverrideWithInheritedBreadcrumbsAttributes extends ControllerBaseWithBreadcrumbsAttributesThatWillBeInherited
{
    #[Get('base-controller-which-will-get-breadcrumbs-inherited-by-child', name: 'base-controller-which-will-get-breadcrumbs-inherited-by-child')]
    public function test()
    {
        $responseData = [
            'controller_action' => self::class . '::' . __FUNCTION__,
            'crumbs' => BreadcrumbsTrail::getCrumbs()
        ];
        
        return new JsonResponse(data: \json_encode($responseData), json: true);
    }
}
