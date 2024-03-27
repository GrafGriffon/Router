<?php

use GrafGriffon\PhpRouter\Route;
use GrafGriffon\PhpRouter\Router;

require_once './vendor/autoload.php';

$router = new Router();

$router->addRoutes(
    Router::prefix('games',
        Router::prefix('v1',
            Route::make('GET', 'get', function () { echo "get\n"; }),
            Route::make('POST', 'add', function () { echo "add\n"; })
        ),
        Route::make('GET', 'documentation', function () { echo "documentation\n"; })
    )
);
$router->addRoute(Route::make('GET', 'example', function () { echo "example\n"; }));

$router->addRoutes(
    Route::make('GET', 'get', function () { echo "get\n"; }),
    Route::make('POST', 'add', function () { echo "add\n"; })
);

$target = $router->match('GET', '/games/v1/get?val=test');

if ($target){
    $target();
} else {
    echo 'Route not present';
}