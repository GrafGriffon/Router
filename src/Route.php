<?php

namespace GrafGriffon\PhpRouter;
class Route
{
    public array $methods;
    public string $path;
    public mixed $target;
    public array $middleware;

    public function __construct(array $methods, string $path, mixed $target, array $middleware = [])
    {
        $this->methods = $methods;
        $this->path = trim($path, '/');;
        $this->target = $target;
        $this->middleware = $middleware;
    }

    public function prefix(string $prefix, array $middleware = []): self
    {
        foreach ($middleware as $element) {
            $this->middleware[] = $element;
        }
        $this->path = (!in_array($prefix, ['', '/']) ? '/' . trim($prefix, '/') . '/' : '') . trim($this->path, '/');
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getTarget(): mixed
    {
        return $this->target;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }
}