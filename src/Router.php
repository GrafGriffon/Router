<?php

namespace GrafGriffon\PhpRouter;

class Router
{
    private array $routes;
    private string $globalGroup;

    public function __construct(array $routes = [], string $globalGroup = '')
    {
        $this->setRoutes($routes);
        $this->setGlobalGroup($globalGroup);
    }

    public function setGlobalGroup(string $globalGroup = ''): void
    {
        $this->globalGroup = $globalGroup;
    }

    public static function group(string $groupName, ...$routes): array
    {
        $routes = self::flattenArray($routes);
        /** @var Route $route */
        foreach ($routes as $route) {
            if ($route instanceof Route) {
                $route->prefix($groupName);
            }
        }
        return $routes;
    }

    public function addRoute(Route $route): void
    {
        $this->routes[] = $route;
    }

    public function addRoutes(...$routes): void
    {
        $routes = self::flattenArray($routes);
        foreach ($routes as $route) {
            $this->routes[] = $route;
        }
    }

    public function setRoutes(array $routes): void
    {
        $this->routes = $routes;
    }

    public function listOfArrays(): array
    {
        return array_map('get_object_vars', $this->routes);
    }

    public function listOfObjects(): array
    {
        return $this->routes;
    }

    public function match($method = null, $path = null): mixed
    {
        $path = $path ?: ($_SERVER['REQUEST_URI'] ?? '/');
        $method = $method ?: ($_SERVER['REQUEST_METHOD'] ?? 'GET');

        $globalGroup = $this->globalGroup != '' && !str_starts_with($this->globalGroup, '/') ? '/' . $this->globalGroup : $this->globalGroup;

        if (($strPos = strpos($path, '?')) !== false) {
            $path = substr($path, 0, $strPos);
        }

        $lastRequestUrlChar = $path ? $path[strlen($path) - 1] : '';

        foreach ($this->listOfArrays() as $handler) {
            list('method' => $routeMethod, 'path' => $routePath, 'target' => $target) = $handler;

            if ($method != $routeMethod) {
                continue;
            }
            if (!str_starts_with($routePath, '/')) {
                $routePath = '/' . $routePath;
            }
            $routePath = $globalGroup . $routePath;

            if (($position = strpos($routePath, '[')) === false) {
                $match = strcmp($path, $routePath) === 0;
            } else {
                if (strncmp($path, $routePath, $position) !== 0 && ($lastRequestUrlChar === '/' || $routePath[$position - 1] !== '/')) {
                    continue;
                }

                $regex = $this->compileRoute($routePath);
                $match = preg_match($regex, $path) === 1;
            }

            if ($match) {
                return $target;
            }
        }

        return false;
    }

    private function compileRoute(string $route): string
    {
        if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?](\?|)`', $route, $matches, PREG_SET_ORDER)) {
            $matchTypes = [
                'i' => '[0-9]++',
                'a' => '[0-9A-Za-z]++',
                'h' => '[0-9A-Fa-f]++',
                '*' => '.+?',
                '**' => '.++',
                '' => '[^/\.]++'
            ];
            foreach ($matches as $match) {
                list($block, $pre, $type, $param, $optional) = $match;

                if (isset($matchTypes[$type])) {
                    $type = $matchTypes[$type];
                }
                if ($pre === '.') {
                    $pre = '\.';
                }

                $optional = $optional !== '' ? '?' : null;

                $pattern = '(?:'
                    . ($pre !== '' ? $pre : null)
                    . '('
                    . ($param !== '' ? "?P<$param>" : null)
                    . $type
                    . ')'
                    . $optional
                    . ')'
                    . $optional;

                $route = str_replace($block, $pattern, $route);
            }
        }
        return "`^$route$`u";
    }

    private static function flattenArray(array $array): array
    {
        $result = [];
        foreach ($array as $element) {
            if (is_array($element)) {
                $result = array_merge($result, self::flattenArray($element));
            } else {
                $result[] = $element;
            }
        }
        return $result;
    }
}