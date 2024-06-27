<?php
use Erickcomp\BreadcrumbAttributes\Facades\CrumbBasket;

/**
 * Use the "putCrumbFor*" methods from the CrumbBasket Facade to add breadcrumbs
 * to controller actions or to routes (by using the route name).
 * 
 * Examples:
 *      // Laravel way for expressing controller actions
 *      CrumbBasket::putCrumbForControllerAction('App\Http\MyController@myMethod1', 'My Method 1', 'start.my-method1', 'start');
 *      
 *      // Array callable syntax with using string class name
 *      CrumbBasket::putCrumbForControllerAction(['App\Http\MyController', 'myMethod2'], 'My Method 2', 'start.my-method2', 'start');
 *      
 *      // Array callable syntax with using ::class "constant" to access class name
 *      CrumbBasket::putCrumbForControllerAction([\App\Http\MyController::class, 'myMethod3'], 'My Method 3', 'start.my-method3', 'start');
 * 
 *      // Route name will be used as breacrumb name
 *      CrumbBasket::putCrumbForRouteName('my-route-1', 'My Method 3', 'start');
 */



