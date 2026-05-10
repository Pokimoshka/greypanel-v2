<?php

declare(strict_types=1);

namespace GreyPanel\Core;

use GreyPanel\Interface\Repository\OnlineRepositoryInterface;
use GreyPanel\Interface\Service\ModuleServiceInterface;
use GreyPanel\Interface\Service\PermissionServiceInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Interface\Service\SettingsServiceInterface;
use GreyPanel\Interface\Service\ThemeServiceInterface;
use GreyPanel\Middleware\PermissionMiddleware;
use GreyPanel\Service\PermissionService;
use GreyPanel\Service\SiteService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;
use Twig\Environment;
use Twig\TwigFunction;

final class App
{
    private Container $container;
    private OnlineRepositoryInterface $onlineRepo;
    private SessionServiceInterface $sessionService;
    private ?LoggerInterface $logger;
    private RouteCollection $routeCollection;
    private array $middlewareMap = [];

    public function __construct(
        RouteCollection $routeCollection,
        Container $container,
        OnlineRepositoryInterface $onlineRepo,
        SessionServiceInterface $sessionService,
        ?LoggerInterface $logger = null
    ) {
        $this->routeCollection = $routeCollection;
        $this->container = $container;
        $this->onlineRepo = $onlineRepo;
        $this->sessionService = $sessionService;
        $this->logger = $logger;
    }

    public function run(): void
    {
        $request = new Request();
        $this->initView($request);

        $user = $this->sessionService->getUser();
        View::addGlobal('app', [
            'user' => $user ? $user->toArray() : null,
            'env' => APP_DEBUG ? 'dev' : 'prod'
        ]);

        if ($this->sessionService->isLoggedIn() && $this->sessionService->getUser() !== null) {
            $this->onlineRepo->updateActivity($this->sessionService->getUser()->getId());
        }

        $this->loadMiddlewareMap(require ROOT_DIR . '/config/middleware.php');

        $response = $this->handleRequest($request);
        $response->send();
    }

    public function loadMiddlewareMap(array $map): void
    {
        $this->middlewareMap = $map;
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
        $siteService = $this->container->get(SiteService::class);
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
        $twig->addGlobal('site_url', $this->container->get(SiteService::class)->getSiteUrl());
        $twig->addFunction(new TwigFunction('has_permission', [$this->container->get(PermissionService::class), 'hasPermission']));
        $twig->addGlobal('flash', [
            'error' => $this->sessionService->getFlash('error'),
            'success' => $this->sessionService->getFlash('success'),
        ]);

        $localeManager = $this->container->get(\GreyPanel\Service\LocaleManager::class);
        $translator = $this->container->get(TranslatorInterface::class);
        $twig->addGlobal('available_languages', $localeManager->getLanguageNames($translator));

        $twig->addFunction(new TwigFunction('trans', function (string $key, array $params = []) use ($translator) {
            return $translator->trans($key, $params);
        }));
    }

    private function handleRequest(Request $request): Response
    {
        try {
            $localeMiddleware = $this->container->get(\GreyPanel\Middleware\LocaleMiddleware::class);

            $response = $localeMiddleware->handle($request, function (Request $request): Response {
                $context = new RequestContext();
                $context->fromRequest($request->getRequest());
                $matcher = new UrlMatcher($this->routeCollection, $context);

                try {
                    $parameters = $matcher->match($request->getPath());
                    $handler = $parameters['_controller'];
                    $middlewares = $parameters['_middleware'] ?? [];
                    unset($parameters['_controller'], $parameters['_middleware'], $parameters['_route']);

                    $finalHandler = function (Request $request) use ($handler, $parameters) {
                        return $this->executeHandler($request, $handler, $parameters);
                    };

                    $pipeline = $this->buildMiddlewarePipeline($middlewares, $finalHandler);
                    return $pipeline($request);
                } catch (ResourceNotFoundException $e) {
                    return $this->renderError(404);
                } catch (MethodNotAllowedException $e) {
                    return $this->renderError(405);
                }
            });

            return $response;
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

        if ($name === 'rate_limit') {
            $middleware = $this->container->get('rate_limit.' . $param);
            return function (Request $request) use ($middleware, $next) {
                return $middleware->handle($request, $next);
            };
        }

        if ($name === 'permission') {
            $permissionService = $this->container->get(PermissionServiceInterface::class);
            $middleware = new PermissionMiddleware($permissionService, $param);
            return function (Request $request) use ($middleware, $next) {
                return $middleware->handle($request, $next);
            };
        }

        $middlewareClass = $this->middlewareMap[$name] ?? null;
        if (!$middlewareClass) {
            throw new \RuntimeException("Unknown middleware alias: {$name}");
        }

        if ($this->container->has($middlewareClass)) {
            $middleware = $this->container->get($middlewareClass);
        } else {
            $middleware = new $middlewareClass();
        }

        return function (Request $request) use ($middleware, $next) {
            return $middleware->handle($request, $next);
        };
    }

    private function executeHandler(Request $request, callable|string $handler, array $vars): Response
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

        foreach ($parameters as $param) {
            $paramType = $param->getType();
            $paramName = $param->getName();

            if ($paramType instanceof \ReflectionNamedType && $paramType->getName() === Request::class) {
                $args[] = $request;
                continue;
            }

            if (array_key_exists($paramName, $routeVars)) {
                $value = $routeVars[$paramName];
                if ($paramType instanceof \ReflectionNamedType) {
                    $value = match ($paramType->getName()) {
                        'int' => (int)$value,
                        'float' => (float)$value,
                        'bool' => (bool)$value,
                        'string' => (string)$value,
                        default => $value,
                    };
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
