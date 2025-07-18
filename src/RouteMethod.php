<?php

namespace GrafGriffon\PhpRouter;

/**
 * @method static Route GET(string $path, mixed $target, array $middleware = []) Returns Route
 * @method static Route POST(string $path, mixed $target, array $middleware = []) Returns Route
 * @method static Route PUT(string $path, mixed $target, array $middleware = []) Returns Route
 * @method static Route PATCH(string $path, mixed $target, array $middleware = []) Returns Route
 * @method static Route DELETE(string $path, mixed $target, array $middleware = []) Returns Route
 */
class RouteMethod
{
    public const GET = 'GET';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const PATCH = 'PATCH';
    public const DELETE = 'DELETE';

    public const METHODS = [self::GET, self::POST, self::PUT, self::PATCH, self::DELETE];

    public static function __callStatic(string $method, array $args): Route
    {
        if (in_array($method, self::METHODS)) {
            return new Route([$method], ...$args);
        }
        throw new \BadMethodCallException("Method $method not allowed.");
    }

    public static function ALL(string $path, mixed $target, array $middleware = []): Route
    {
        return new Route(self::METHODS, $path, $target, $middleware);
    }

    public static function METHODS(array $methods, string $path, mixed $target, array $middleware = []): Route
    {
        if ($diff = array_diff($methods, self::METHODS)) {
            throw new \BadMethodCallException("Method " . implode(',', $diff) . " not allowed.");
        }
        return new Route($methods, $path, $target, $middleware);
    }
}