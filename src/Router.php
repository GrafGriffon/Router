<?php

namespace GrafGriffon\PhpRouter;

class Router
{
    const MATCH_TYPES = [
        'i' => '[0-9]++',
        'a' => '[0-9A-Za-z]++',
        'h' => '[0-9A-Fa-f]++',
        '*' => '.+?',
        '**' => '.++',
        '' => '[^/\.]++'
    ];

    protected array $routes = [];
    protected array $middleware = [];
    protected string $basePath = '';

    public function __construct(array $routes = [], $basePath = '', $middleware = [])
    {
        $this->add(...$routes);
        $this->basePath = '/' . ltrim($basePath, '/');
        $this->middleware = $middleware;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function add(...$routes): void
    {
        foreach ($routes as $route) {

            if(is_array($route)){
                foreach ($this->flattenArray($route) as $subRoute) {
                    if ($subRoute instanceof Route){
                        $this->routes[] = $subRoute;
                    } else {
                        throw new \BadMethodCallException("Isset bad routes.");
                    }
                }
            } elseif ($route instanceof Route){
                $this->routes[] = $route;
            } else {
                throw new \BadMethodCallException("Isset bad routes.");
            }
        }
    }

    public function match($requestUrl = null, $requestMethod = null)
    {

        $params = [];

        // set Request Url if it isn't passed as parameter
        if ($requestUrl === null) {
            $requestUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        }

        // strip base path from request url
        $requestUrl = substr($requestUrl, strlen($this->basePath));

        // Strip query string (?a=b) from Request Url
        if (($strpos = strpos($requestUrl, '?')) !== false) {
            $requestUrl = substr($requestUrl, 0, $strpos);
        }

        $lastRequestUrlChar = $requestUrl ? $requestUrl[strlen($requestUrl) - 1] : '';

        // set Request Method if it isn't passed as a parameter
        if ($requestMethod === null) {
            $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        }
        $requestUrl = trim($requestUrl, '/');

        /** @var Route $handler */
        foreach ($this->routes as $handler) {
            $route = $handler->getPath();

            if (!in_array($requestMethod, $handler->getMethods())) {
                continue;
            }

            if (($position = strpos($route, '{')) === false) {
                // No params in url, do string comparison
                $match = strcmp($requestUrl, $route) === 0;
            } else {
                // Compare longest non-param string with url before moving on to regex
                // Check if last character before param is a slash, because it could be optional if param is optional too (see https://github.com/dannyvankooten/AltoRouter/issues/241)
                if (strncmp($requestUrl, $route, $position) !== 0 && ($lastRequestUrlChar === '/' || $route[$position - 1] !== '/')) {
                    continue;
                }

                $regex = $this->compileRoute($route);
                $match = preg_match($regex, $requestUrl, $params) === 1;
            }

            if ($match) {
                if ($params) {
                    foreach ($params as $key => $value) {
                        if (is_numeric($key)) {
                            unset($params[$key]);
                        }
                    }
                }

                return [
                    'route' => $handler,
                    'params' => $params
                ];
            }
        }

        return false;
    }

    protected function compileRoute($route)
    {
        if (preg_match_all('`(/|\.|)\{([^:\}]*+)(?::([^:\}]*+))?\}`', $route, $matches, PREG_SET_ORDER)) {
            $matchTypes = self::MATCH_TYPES;
            foreach ($matches as $match) {

                if (isset($match[3])){
                    list($block, $pre, $type, $param) = $match;
                } else {
                    list($block, $pre, $param) = $match;
                    $type = '';
                }

                if (isset($matchTypes[$type])) {
                    $type = $matchTypes[$type];
                }
                if ($pre === '.') {
                    $pre = '\.';
                }

                //Older versions of PCRE require the 'P' in (?P<named>)
                $pattern = '(?:'
                    . ($pre !== '' ? $pre : null)
                    . '('
                    . ($param !== '' ? "?P<$param>" : null)
                    . $type
                    . '))';

                $route = str_replace($block, $pattern, $route);
            }
        }
        return "`^$route$`u";
    }

    public static function group(string $groupName, array $middleware, ...$routes): array
    {
        $routes = self::flattenArray($routes);
        foreach ($routes as $route) {
            if ($route instanceof Route) {
                $route->prefix($groupName, $middleware);
            } else {
                throw new \BadMethodCallException("Isset bad routes.");
            }
        }
        return $routes;
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