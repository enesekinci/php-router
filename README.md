# php-router

## composer require enesekinci/php-router

# the router like laravel router that we can use in our own projects but it's so basic

```php
<?php

require_once "vendor/autoload.php";

use EnesEkinci\PhpRouter\Middleware\Test;
use EnesEkinci\PhpRouter\Middleware\Json;
use EnesEkinci\PhpRouter\MiddlewareGroup;
use EnesEkinci\PhpRouter\Request;
use EnesEkinci\PhpRouter\Response;
use EnesEkinci\PhpRouter\Router;

$request = new Request();
$response = new Response();
$router = new Router($request, $response);

$router->modelBindingNamespace('EnesEkinci\PhpRouter\Models');

$router->setNotFoundContent('404 Not Found');

MiddlewareGroup::middleware([
    'test' => Test::class,
    'json' => Json::class,
]);

Router::redirect('/galatasaray/{slug}', '/');

Router::get('/*', function () {
    dd("*****");
});

Router::prefix('/php')->name('php.')->middleware('test', 'json')->group(function () {
    Router::get('/group-1', function () {
        echo "group1";
    })->name('group-1');
    Router::get('/group-2', function () {
        echo "group2";
    })->name('group-2');
    Router::get('/group-3', function () {
        echo "group3";
    })->name('group-3');
});


Router::get('/{best}', function (Request $request, $test = 123, $best1, Response $response) {
    $route = Router::currentRouteAction();
})->middleware('web');

Router::get('/{user:id}', function (Request $request, $user, Response $response) {
    dd($request, "index", $user, $response);
})->middleware('json', 'test')->name('index');


$router->resolve();
```
