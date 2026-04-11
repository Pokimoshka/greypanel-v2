<?php
declare(strict_types=1);

namespace GreyPanel\Core;

class Route
{
    private array $methods;
    private string $uri;
    private $handler;
    private array $middleware = [];

    public function __construct(array $methods, string $uri, $handler)
    {
        $this->methods = $methods;
        $this->uri = $uri;
        $this->handler = $handler;
    }

    public function addMiddleware(string $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }
}