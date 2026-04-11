<?php
declare(strict_types=1);

namespace GreyPanel\Core;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector as FastRouteCollector;

class Router
{
    private Container $container;
    private Dispatcher $dispatcher;
    private RouteCollector $routeCollector;
    private array $routes = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function load(string $routesFile): void
    {
        $this->dispatcher = \FastRoute\simpleDispatcher(function (FastRouteCollector $r) use ($routesFile) {
            $this->routeCollector = new RouteCollector($r);
            $routes = require $routesFile;
            $routes($this->routeCollector);
        });
        $this->routes = $this->routeCollector->getRoutes();
    }

    public function dispatch(Request $request): array
    {
        $routeInfo = $this->dispatcher->dispatch($request->getMethod(), $request->getPath());
        if ($routeInfo[0] === Dispatcher::FOUND) {
            /** @var Route $route */
            $route = $routeInfo[1];
            return [
                $routeInfo[0],
                $route->getHandler(),
                $routeInfo[2],
                $route->getMiddleware(),
            ];
        }
        return $routeInfo;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}