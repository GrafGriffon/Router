# GrafGriffon/PhpRouter

Powerful, flexible web routing for your service.

## Installation and Autoloading
This package is installable and PSR-4 autoloadable via Composer as [grafgriffon/php-router][].

Alternatively, [download a release][], or clone this repository, then map the
`GrafGriffon\PhpRouter\` namespace to the package `src/` directory.

## Dependencies

This package requires PHP 8.0 or later. It has been tested on PHP 8.0-8.3. We recommend using the latest available version of PHP as a matter of principle.

## Example using

```php
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
```