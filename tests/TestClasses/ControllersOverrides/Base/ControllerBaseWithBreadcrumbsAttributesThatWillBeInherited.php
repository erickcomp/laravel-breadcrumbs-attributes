<?php

namespace ErickComp\BreadcrumbAttributes\Tests\TestClasses\ControllersOverrides\Base;

use ErickComp\BreadcrumbAttributes\Attributes\Breadcrumb;
use ErickComp\BreadcrumbAttributes\Facades\BreadcrumbsTrail;
use Illuminate\Http\JsonResponse;
use Spatie\RouteAttributes\Attributes\Get;

class ControllerBaseWithBreadcrumbsAttributesThatWillBeInherited
{
    #[Get('base-controller-which-will-get-breadcrumbs-inherited-by-child', name: 'base-controller-which-will-get-breadcrumbs-inherited-by-child')]
    #[Breadcrumb('Breadcrumb defined at a BASE_CONTROLLER', name: 'base-controller-which-will-get-breadcrumbs-inherited-by-child')]
    public function test()
    {
        $responseData = [
            'only_parent_data' => 'only_parent_data',
            'controller_action' => self::class . __METHOD__,
            'crumbs' => BreadcrumbsTrail::getCrumbs()
        ];
        
        return new JsonResponse(data: \json_encode($responseData), json: true);
    }
}
