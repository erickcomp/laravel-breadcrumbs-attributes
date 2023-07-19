<?php
namespace ErickComp\BreadcrumbAttributes\Tests\TestClasses;

use ErickComp\BreadcrumbAttributes\Facades\BreadcrumbsTrail;
use Illuminate\Http\JsonResponse;

trait SendsBreadcrumbsAsJsonResponse
{
    public function sendsBreadcrumbsAsJsonResponse()
    {
        return new JsonResponse(data: \json_encode(BreadcrumbsTrail::getCrumbs()), json: true);
    }
}
