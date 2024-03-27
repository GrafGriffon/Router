<?php
require 'Router.php';
require 'Route.php';

$router = new Router();

$router->addRoutes(
    Router::prefix('favorite-games',
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

$target = $router->match('GET', '/favorite-games/v1/get?val=test');

if ($target){
    $target();
} else {
    echo 'Route not present';
}