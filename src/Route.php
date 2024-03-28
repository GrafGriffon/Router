<?php

namespace GrafGriffon\PhpRouter;
class Route
{
    public string $method;
    public string $path;
    public mixed $target;

    public function __construct(string $method, string $path, mixed $target)
    {
        $this->method = $method;
        $this->path = $path;
        $this->target = $target;
    }

    public static function make(string $method, string $path, mixed $target): self
    {
        return new self($method, $path, $target);
    }

    public function prefix(string $prefix): self
    {
        $this->path = $this->path != '' ? "$prefix/$this->path" : $prefix;
        return $this;
    }
}