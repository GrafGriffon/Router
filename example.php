<?php

use GrafGriffon\PhpRouter\RouteMethod;
use GrafGriffon\PhpRouter\Router;

require_once './vendor/autoload.php';

$router = new Router(
    Router::group(
        '',
        [],
        Router::group('v1', [],
            RouteMethod::GET('get', function () { echo "get {domain}/optional-base-path/v1/get"; }),
            RouteMethod::POST('post', function () { echo "post {domain}/optional-base-path/v1/post"; }),
        ),
        Router::group('v2', [],
            RouteMethod::PUT('put', function () { echo "put {domain}/optional-base-path/v2/put"; }),
            RouteMethod::DELETE('delete', function () { echo "delete {domain}/optional-base-path/v2/delete"; }),
            Router::group('test', [],
                RouteMethod::PATCH('patch', function () { echo "patch {domain}/optional-base-path/v2/test/patch"; }),
            )
        )

    ),
    'optional-base-path'
);

$router->add(RouteMethod::GET('/documentation', function () { echo "get {domain}/optional-base-path/documentation"; }));

if (list('route' => $route, 'params' => $params) = $router->match()) {
    $target = $route->getTarget();
    foreach ($route->getMiddleware() as $middleware) {
        (new $middleware())();
    }
    $target();
} else {
    echo 'Route not present';
}