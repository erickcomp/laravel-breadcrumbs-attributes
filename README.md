<p align="center">
    <a href="https://packagist.org/packages/erickcomp/laravel-breadcrumbs-attributes"><img src="https://img.shields.io/packagist/v/erickcomp/laravel-breadcrumbs-attributes" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/erickcomp/laravel-breadcrumbs-attributes"><img src="https://img.shields.io/packagist/dt/erickcomp/laravel-breadcrumbs-attributes" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/erickcomp/laravel-breadcrumbs-attributes"><img src="https://img.shields.io/packagist/l/erickcomp/laravel-breadcrumbs-attributes" alt="License"></a>
</p>

# Use PHP 8 attributes to create breadcrumbs for controllers actions in a Laravel app

This package provides PHP 8 Attribute classes to automatically create breadcrumbs for your controller actions.
This package was inspired by Spatie's wonderful [Laravel Routes Attributes](https://github.com/spatie/laravel-route-attributes#use-php-8-attributes-to-register-routes-in-a-laravel-app).
In fact, a big portion of the "controller methods discovery" was copied from Laravel Routes Attributes package (Thanks for that, Spatie fellows!).

## How it works

It works by scanning the controllers in the directories that contains your controllers and putting into a "Breadcrumb basket" all the crumbs you put in your controller actions and making it available at your controllers and views through DI or the `\ErickComp\BreadcrumbAttributes\Facades\BreadcrumbsTrail` facade.

## Custom controllers directories
If, for some, reason, you keep your controllers in a different directory, fear not! All you have to do is publish the config file and write down the directories where your controllers lie.

## Collision when defining breadcrumbs
If you define breadcrumbs for the same route twice, and exception will be thrown. This behavior can be changed through the config ```erickcomp-laravel-breadcrumbs-attributes.when_already_defined```. This config accepts one of these values:

* ```throw```: It's the default behavior. An exception will be thrown when the breadcrumbs scanner finds the second definition of the breadcrumb;

* ```ignore```: The <b>first</b> definition of the breadcrumb will be used and the following ones will be <b>silently ignored</b>. It's useful to prevent a definition from a base controller class to be overriden by children controller classes.
 
* ```override```: The <b>last</b> definition of the breadcrumb will be used and the previous ones following will be <b>silently ignored</b>. It's useful to create a fallback breadcrumb definition on a base controller class while allowing it to be overriden into a child controller class.


## Installation

You can install the package via composer:

```bash
composer require erickcomp/laravel-breadcrumbs-attributes
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="ErickComp\BreadcrumbAttributes\Providers\RouteAttributesServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
<?php

use ErickComp\BreadcrumbAttributes\Providers\BreadcrumbsAttributeServiceProvider;

return [
    'controller_directories' => [
        app_path('Http/Controllers')
    ],
    'breadcrumbs_files' => [
        BreadcrumbsAttributeServiceProvider::defaultAttributelessBreadcrumbsFile()
    ]
];


```

Here you can customize the directories that will be scanned at your will and customize the file(s) that can contain Breadcrumbs declarations that do not use attributes (are done like standard route declarations).

## Usage

The package provides an attribute (`\ErickComp\BreadcrumbAttributes\Attributes\Breadcrumb`) that can handle breadcrumbs labels.
It can even handle breadcrumbs that require information that are available to the controller method you want to create a breadcrumb trail.

The Breadcrumb attribute can hold the following info:

- label
- parent
- name
- before (a crumb that will be inserted before the current crumb)
- after (a crumb that will be inserted after the current crumb)
  
You did not see the url of the crumb, right? It's because the URL of the crumb is evaluated based on the controller method the attribute is attached. If you create a Breadcrumb attribute and try to use it on a trail without referencing it into a route, an Exception will be thrown.

### Adding a breadcrumb with a simple string label

```php
use ErickComp\BreadcrumbAttributes\Attributes\Breadcrumb;

class MyController
{
    #[Breadcrumb(label:'Home', name: 'home')]
    public function home()
    {
    }

    #[Breadcrumb(label:'My Data list', parent:'home', name:'home.my-data-list')]
    public function myMethod()
    {
    }
}
```
Supposing you registered the routes like this:

```php
Route::get('/', [MyController::class, 'home']);
Route::get('my-route', [MyController::class, 'myMethod']);
```

And the current route in your browser is "http://localhost/my-route"

These attributes will automatically create breadcrumbs like:

```php
array(6) {
  [0]=>
  object(ErickComp\BreadcrumbAttributes\ProcessedCrumb)#336 (2) {
    ["label"]=>
    string(4) "Home"
    ["url"]=> 
    'http://localhost'
  }
  [1]=>
  object(ErickComp\BreadcrumbAttributes\ProcessedCrumb)#334 (2) {
    ["label"]=>
    string(7) "My Data list"
    ["url"]=>
    string(43) "http://localhost/my-route"
  }
```

### Creating complex breadcrumbs based on the Request data
You have all the arguments passed to the method to which the breadcrumb is attached at your disposal in order to generate the ideal label for your breadcrumb. It's done by using the same patterns used on route definitions (enclosing route/controller methods on curly braces) to acccess the route parameters on the breadcrumb label. Here's an example:

You have worked the routes definitions this way:

```php
Route::get('/', [MyController::class, 'home']);
Route::get('/users/list', [MyController::class, 'users-list']);
Route::get('users/show/{user}', [MyController::class, 'user-show']);
```

And you want the `user-show` breadcrumb label to be like 'Showing user: "John Doe"'

You would need to define your breadcrumbs somewhat this way:

Controller with breadcrumbs attributes:
```php
use ErickComp\BreadcrumbAttributes\Attributes\Breadcrumb;

class MyController
{
    #[Breadcrumb(label:'Home', name: 'home')]
    public function home()
    {
    }

    #[Breadcrumb(label:'Users List', parent:'home', name:'home.users-list')]
    public function usersList()
    {
    }

    #[Breadcrumb(label: 'Showing user: "{user}->name"', parent: 'home.users-list', name: 'home.user-list')]
    public function showUser(UserModel $user)
    {
    }
}
```

The generated breadcrumbs will be like:

```php
array(6) {
  [0]=>
  object(ErickComp\BreadcrumbAttributes\ProcessedCrumb)#336 (2) {
    ["label"]=>
    string(4) "Home"
    ["url"]=> 
    'http://localhost'
  }
  [1]=>
  object(ErickComp\BreadcrumbAttributes\ProcessedCrumb)#334 (2) {
    ["label"]=>
    string(7) "Users list"
    ["url"]=>
    string(43) "http://localhost/users/list"
  },
  [2]=>
  object(ErickComp\BreadcrumbAttributes\ProcessedCrumb)#332 (2) {
    ["label"]=>
    string(7) "Showing user: "John Doe""
    ["url"]=>
    string(43) "http://localhost/users/show/52"
  }

```

### The route parameter resolution
The breadcrumbs links deal with the url params and generate the url's using them, so you (most probably) won't have to deal with url generation for your breadcrumbs.

### Aux breadcrumbs
Aux breadcrumbs are used to create "logical" breadcrumbs. They can be used to express menus, for example.
Let's say you have a menu like this:
```
├── Home
├── Admin/
│   └── Users
├── Catalog
└── ...
```

Your breadcrumbs for the "user-show" could be expressed like this:

Home > Admin > Users > Showing user "John Doe"

In this case, the "Admin" crumb does not have a corresponding url, but it makes sense in the breadcrumbs path

In order to insert this "Admin" crumb, you could rewrite the above example like this:

```php
use ErickComp\BreadcrumbAttributes\Attributes\Breadcrumb;

class MyController
{
    #[Breadcrumb(label:'Home', name: 'home')]
    public function home()
    {
    }

    #[Breadcrumb(label:'Users List', parent:'home', name:'home.users-list')]
    public function usersList()
    {
    }

    #[Breadcrumb(before: 'Admin', label: 'Showing user: "{user}->name"',  parent: 'home.users-list', name: 'home.user-list')]
    public function showUser(UserModel $user)
    {
    }
}
```

## Integration with other packages (Spatie's Laravel Route Attributes)

As mentioned before, this package was inspired by by Spatie's wonderful [Laravel Routes Attributes](https://github.com/spatie/laravel-route-attributes#use-php-8-attributes-to-register-routes-in-a-laravel-app). In fact, I have designed this package to work alongside Spatie's package, so it has some built-in integrations with it:

- If you're using custom directories for the controllers and you have defined them in the `route-attributes` config file, you don't have to redefine it again in the breadcrumbs config, because it tries to read the one from spatie's route attributes;
- If you are naming your routes (and I advise you to do so!) you can omit the "name" argument of the Breadcrumb attribute, as the Breadcrumb is going to use the one defined in the route attribute.

Example:

```php
use ErickComp\BreadcrumbAttributes\Attributes\Breadcrumb;
use Spatie\RouteAttributes\Attributes\Get;

class MyController
{
    #[Get('/', name: 'home')]
    #[Breadcrumb(label:'Home')]
    public function home()
    {
    }

    #[Get('/users/list', name: 'home.users-list')]
    #[Breadcrumb(label:'Users List', parent:'home')]
    public function usersList()
    {
    }

    
    #[Get('/users/show/{user}', name: 'home.users-list.user-show')]
    #[Breadcrumb(label: 'Showing user: "{user}->name"', parent: 'home.users-list')]
    public function showUser(UserModel $user)
    {
    }
}
```

## Blade integration
This packages also provides a blade component that renders the breadcrumbs from the current route.
It should be used like this inside your blade template:

```blade
<ul>
    <x-erickcomp-breadcrumbs />
</ul>
```

To define classes of list items, you can specify:
```blade
<x-erickcomp-breadcrumbs
  class="item"
  activeClass="active"
/>
```

This style of breadcrumbs component was made from the also awesome package [Tabuna Breadcrumbs](https://github.com/tabuna/breadcrumbs#introduction). Before coding this package, my intent was to use Tabuna breadcrumbs, but we cannot use closures on PHP attributes. But I got some concepts from there (and the component code) to build my own package as well.

## Caching
This package works by checking the controller directories on every request and gather all the breadcrumbs information into our breadcrumb basket. Then, when requested by the programer (through the Trail class, the Breadcrumbs facade or the by using the erickcomp-breadcrumbs blade component), the breadcrumb trail is built based on the previously collected breadcrumbs. But in production mode, the breadcrumbs should not change, so we could entirely skip this gathering of breadcrumbs step. For that caching management, the package provides 2 artisan commands:

```bash
erickcomp:laravel-breadcrumbs-attributes:cache
```
and

```bash
erickcomp:laravel-breadcrumbs-attributes:clear-cache
```

# Defining breadcrumbs without using attributes
If, for some reason you need declare breadcrumbs without using controller attributes, you can leverage the ```CrumbBasket``` facade.
It has methods to "put" crumbs into basket. By default, you can define breadcrumbs this way on the file ``` routes/breadcrumbs.php``` .
It can be changed by publishing the config file and changing the "breadcrumbs_files" entry. In this file, you can define breadcrumbs like:

```php
// Laravel way for expressing controller actions
CrumbBasket::putCrumbForControllerAction('App\Http\MyController@myMethod1', 'My Method 1', 'start.my-method1', 'start');

// Array callable syntax with using string class name
CrumbBasket::putCrumbForControllerAction(['App\Http\MyController', 'myMethod2'], 'My Method 2', 'start.my-method2', 'start');

// Array callable syntax with using ::class "constant" to access class name
CrumbBasket::putCrumbForControllerAction([\App\Http\MyController::class, 'myMethod3'], 'My Method 3', 'start.my-method3', 'start');

// Route name will be used as breacrumb name
CrumbBasket::putCrumbForRouteName('my-route-1', 'My Method 3', 'start');
```

You can manually create this ```routes/breadcrumbs.php``` file or you can use the command ```php artisan vendor:publish --tag=attributeless-breadcrumbs```

## Version 1

The first version of this package used other attribute classes to access the route/controller parameters. It worked well, but was too verbose. Now, all the features provided by those classes were migrated to the new ```{routeParam}``` syntax, that's more aligned to the Laravel's syntax to express route parameters on the route declarations.

## Version 2

The version 2 of this package used "before" and "after" parameter names in the breadcrumb
definition. After some time using it, it started to feel clunky and I decided to change it only to "before" and after. As this is a breaking change, the version was bumped to 3.

## Credits

I have searched and tested several routes and breadcrumbs packages and I liked Spatie's Laravel Route Attributes and Tabuna Breadcrumbs the most. As I was unable to make them work together organically,
I decided to create my own package based on both of their ideas (and pieces of code. Once again, thank you both for that) and create something totally functional using PHP 8 Attributes.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
