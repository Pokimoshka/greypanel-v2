<?php

declare(strict_types=1);

namespace GreyPanel\Core;

use FastRoute\Dispatcher;
use GreyPanel\Interface\Repository\OnlineRepositoryInterface;
use GreyPanel\Interface\Service\ModuleServiceInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Interface\Service\SettingsServiceInterface;
use GreyPanel\Interface\Service\ThemeServiceInterface;
use GreyPanel\Service\PermissionService;
use Psr\Log\LoggerInterface;
use Throwable;
use Twig\Environment;
use Twig\TwigFunction;

final class App
{
    private Router $router;
    private Container $container;
    private OnlineRepositoryInterface $onlineRepo;
    private SessionServiceInterface $sessionService;
    private ?LoggerInterface $logger;

    public function __construct(
        Router $router,
        Container $container,
        OnlineRepositoryInterface $onlineRepo,
        SessionServiceInterface $sessionService,
        ?LoggerInterface $logger = null
    ) {
        $this->router = $router;
        $this->container = $container;
        $this->onlineRepo = $onlineRepo;
        $this->sessionService = $sessionService;
        $this->logger = $logger;
    }

    public function run(): void
    {
        $this->boot();
        $request = new Request();
        $this->initView($request);

        View::addGlobal('app', [
            'user' => $this->sessionService->getUser(),
            'env' => APP_DEBUG ? 'dev' : 'prod'
        ]);

        if ($this->sessionService->isLoggedIn()) {
            $this->onlineRepo->updateActivity($this->sessionService->getUserId());
        }

        $response = $this->handleRequest($request);
        $response->send();
    }

    private function boot(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name($_ENV['SESSION_NAME'] ?? 'greysession');
            $this->sessionService->start();
        }
    }

    private function initView(Request $request): void
    {
        $isAdmin = str_starts_with($request->getPath(), '/admin');
        $themeService = $this->container->get(ThemeServiceInterface::class);

        $templatePath = $isAdmin
            ? __DIR__ . '/../../public/themes/admin/tpl'
            : $themeService->getTemplatePath();

        $cachePath = __DIR__ . '/../../var/cache/twig';
        $debug = APP_DEBUG;

        View::init($templatePath, $cachePath, $debug);

        $twig = View::getTwig();
        $this->registerTwigExtensions($twig, $themeService, $isAdmin);
    }

    private function registerTwigExtensions(
        Environment $twig,
        ThemeServiceInterface $themeService,
        bool $isAdmin
    ): void {
        $siteService = $this->container->get(\GreyPanel\Service\SiteService::class);
        $twig->addFunction(new TwigFunction('url', function (string $path = '') use ($siteService) {
            return rtrim($siteService->getSiteUrl(), '/') . '/' . ltrim($path, '/');
        }));

        $settings = $this->container->get(SettingsServiceInterface::class);
        $twig->addGlobal('site_name', $settings->get('site_name', 'GreyPanel'));
        $twig->addGlobal('active_theme', $settings->get('active_theme', 'default'));
        $twig->addGlobal('theme_url', $isAdmin ? '/themes/admin/assets' : $themeService->getPublicPath());
        $moduleService = $this->container->get(ModuleServiceInterface::class);
        $twig->addFunction(new TwigFunction('module_enabled', [$moduleService, 'isEnabled']));
        $twig->addGlobal('csrf_token', $this->sessionService->getCsrfToken());
        $twig->addGlobal('site_url', $this->container->get(\GreyPanel\Service\SiteService::class)->getSiteUrl());
        $twig->addFunction(new TwigFunction('has_permission', [$this->container->get(PermissionService::class), 'hasPermission']));
    }

    private function handleRequest(Request $request): Response
    {
        try {
            $routeInfo = $this->router->dispatch($request);
            $status = $routeInfo[0];

            switch ($status) {
                case Dispatcher::NOT_FOUND:
                    return $this->renderError(404);
                case Dispatcher::METHOD_NOT_ALLOWED:
                    return $this->renderError(405);
                case Dispatcher::FOUND:
                    $handler = $routeInfo[1];
                    $vars = $routeInfo[2];
                    $middlewares = $routeInfo[3] ?? [];

                    $finalHandler = function (Request $request) use ($handler, $vars) {
                        return $this->executeHandler($request, $handler, $vars);
                    };

                    $pipeline = $this->buildMiddlewarePipeline($middlewares, $finalHandler);
                    return $pipeline($request);
                default:
                    return $this->renderError(500);
            }
        } catch (Throwable $e) {
            $this->logger?->error('Unhandled exception: ' . $e->getMessage(), [
                'exception' => $e,
                'url' => $request->getPath(),
                'method' => $request->getMethod(),
            ]);
            return $this->renderError(500, $e);
        }
    }

    private function buildMiddlewarePipeline(array $middlewares, callable $finalHandler): callable
    {
        $next = $finalHandler;
        foreach (array_reverse($middlewares) as $mw) {
            $next = $this->makeMiddleware($mw, $next);
        }
        return $next;
    }

    private function makeMiddleware(string $middlewareDef, callable $next): callable
    {
        $parts = explode(':', $middlewareDef);
        $name = $parts[0];
        $param = $parts[1] ?? null;

        // Специальная обработка для rate_limit
        if ($name === 'rate_limit') {
            $rateLimitKey = $param;
            $middleware = $this->container->get('rate_limit.' . $rateLimitKey);
            return function (Request $request) use ($middleware, $next) {
                return $middleware->handle($request, $next);
            };
        }

        // Определяем класс middleware
        $className = str_replace('_', '', ucwords($name, '_'));
        $middlewareClass = 'GreyPanel\\Middleware\\' . $className . 'Middleware';

        // Для permission – используем контейнер с передачей параметра
        if ($name === 'permission') {
            // Получаем PermissionService из контейнера
            $permissionService = $this->container->get(\GreyPanel\Service\PermissionService::class);
            $middleware = new $middlewareClass($permissionService, $param);
            return function (Request $request) use ($middleware, $next) {
                return $middleware->handle($request, $next);
            };
        }

        // Для role (если ещё остались старые маршруты, лучше заменить на permission)
        if ($name === 'role') {
            $param = (int)$param;
            $middleware = new $middlewareClass($param);
            return function (Request $request) use ($middleware, $next) {
                return $middleware->handle($request, $next);
            };
        }

        // Для остальных middleware – пытаемся взять из контейнера
        if ($this->container->has($middlewareClass)) {
            $middleware = $this->container->get($middlewareClass);
        } else {
            if ($param !== null) {
                $middleware = new $middlewareClass($param);
            } else {
                $middleware = new $middlewareClass();
            }
        }

        return function (Request $request) use ($middleware, $next) {
            return $middleware->handle($request, $next);
        };
    }

    private function executeHandler(Request $request, $handler, array $vars): Response
    {
        if (is_string($handler)) {
            return $this->callController($request, $handler, $vars);
        }

        if (is_callable($handler)) {
            return $handler($request, ...array_values($vars));
        }

        throw new \RuntimeException('Invalid route handler');
    }

    private function callController(Request $request, string $handler, array $vars): Response
    {
        $parts = explode('@', $handler);
        if (count($parts) !== 2) {
            throw new \RuntimeException('Invalid controller format, expected "Controller@method"');
        }

        $controllerClass = 'GreyPanel\\Controller\\' . $parts[0];
        $method = $parts[1];

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller not found: {$controllerClass}");
        }

        $controller = $this->container->get($controllerClass);

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Method not found: {$method} in {$controllerClass}");
        }

        $args = $this->resolveMethodArguments($controllerClass, $method, $request, $vars);

        return $controller->$method(...$args);
    }

    private function resolveMethodArguments(string $controllerClass, string $method, Request $request, array $routeVars): array
    {
        $reflectionMethod = new \ReflectionMethod($controllerClass, $method);
        $parameters = $reflectionMethod->getParameters();
        $args = [];
        $routeValues = array_values($routeVars);
        $routeIndex = 0;

        foreach ($parameters as $param) {
            $paramType = $param->getType();
            $paramName = $param->getName();

            if ($paramType && $paramType->getName() === Request::class) {
                $args[] = $request;
                continue;
            }

            if ($routeIndex < count($routeValues)) {
                $value = $routeValues[$routeIndex++];
                if ($paramType instanceof \ReflectionNamedType) {
                    $typeName = $paramType->getName();
                    if ($typeName === 'int') {
                        $value = (int)$value;
                    } elseif ($typeName === 'float') {
                        $value = (float)$value;
                    } elseif ($typeName === 'bool') {
                        $value = (bool)$value;
                    } elseif ($typeName === 'string') {
                        $value = (string)$value;
                    }
                }
                $args[] = $value;
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new \RuntimeException("Cannot resolve parameter '{$paramName}' for {$controllerClass}::{$method}");
            }
        }

        return $args;
    }

    private function renderError(int $statusCode, ?Throwable $exception = null): Response
    {
        if ($exception && APP_DEBUG) {
            return new Response($this->formatDebugError($exception), $statusCode);
        }

        $template = "errors/{$statusCode}.tpl";
        if (View::getTwig()->getLoader()->exists($template)) {
            $content = View::render($template, ['status_code' => $statusCode]);
            return new Response($content, $statusCode);
        }

        $defaultMessage = match ($statusCode) {
            404 => 'Страница не найдена',
            405 => 'Метод не поддерживается',
            default => 'Внутренняя ошибка сервера',
        };
        return new Response($defaultMessage, $statusCode);
    }

    private function formatDebugError(Throwable $e): string
    {
        $message = get_class($e) . ': ' . $e->getMessage();
        $file = $e->getFile() . ':' . $e->getLine();
        $trace = nl2br(htmlspecialchars($e->getTraceAsString()));
        return "<h1>Ошибка</h1><p><strong>{$message}</strong></p><p>Файл: {$file}</p><pre>{$trace}</pre>";
    }
}
