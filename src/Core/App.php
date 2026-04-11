<?php
declare(strict_types=1);

namespace GreyPanel\Core;

use FastRoute\Dispatcher;
use GreyPanel\Repository\OnlineRepositoryInterface;
use GreyPanel\Service\SessionService;
use GreyPanel\Service\SessionServiceInterface;
use GreyPanel\Service\ThemeServiceInterface;
use GreyPanel\Service\ModuleServiceInterface;
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
        $twig->addFunction(new TwigFunction('url', function (string $path = '') {
            return rtrim($_ENV['SITE_URL'] ?? '', '/') . '/' . ltrim($path, '/');
        }));


        $twig->addGlobal('site_name', $_ENV['SITE_NAME'] ?? 'GreyPanel');
        $twig->addGlobal('app', [
            'user' => $_SESSION['user'] ?? null,
            'env' => APP_DEBUG ? 'dev' : 'prod'
        ]);
        $twig->addGlobal('active_theme', $themeService->getActiveTheme());
        $twig->addGlobal('theme_url', $isAdmin ? '/themes/admin/assets' : $themeService->getPublicPath());
        $moduleService = $this->container->get(ModuleServiceInterface::class);
        $twig->addFunction(new TwigFunction('module_enabled', [$moduleService, 'isEnabled']));
        $twig->addGlobal('csrf_token', $this->sessionService->getCsrfToken());
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
        $middlewareClass = 'GreyPanel\\Middleware\\' . ucfirst($name) . 'Middleware';

        return function (Request $request) use ($middlewareClass, $param, $next) {
            if ($param !== null) {
                if ($middlewareClass === 'GreyPanel\\Middleware\\RoleMiddleware') {
                    $param = (int)$param;
                }
                $middleware = new $middlewareClass($param);
            } elseif ($this->container->has($middlewareClass)) {
                $middleware = $this->container->get($middlewareClass);
            } else {
                $middleware = new $middlewareClass();
            }

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

        return $controller->$method($request, ...array_values($vars));
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