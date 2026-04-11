<?php

namespace GreyPanel\Core;

use FastRoute\RouteCollector as FastRouteCollector;

class RouteCollector
{
    private FastRouteCollector $collector;
    private array $routes = [];

    public function __construct(FastRouteCollector $collector)
    {
        $this->collector = $collector;
    }

    /**
     * @param string|string[] $method
     */
    public function addRoute($method, string $uri, $handler): Route
    {
        $methods = (array) $method;
        $route = new Route($methods, $uri, $handler);
        foreach ($methods as $m) {
            $this->collector->addRoute($m, $uri, $route);
        }
        $this->routes[] = $route;
        return $route;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}