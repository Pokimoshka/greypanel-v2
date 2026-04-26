<?php

declare(strict_types=1);

use GreyPanel\Command\CronCommand;
use GreyPanel\Controller\AdminController;
use GreyPanel\Controller\AdminForumController;
use GreyPanel\Controller\AdminModuleController;
use GreyPanel\Controller\AdminNewsController;
use GreyPanel\Controller\AdminSeoController;
use GreyPanel\Controller\AdminServerSettingsController;
use GreyPanel\Controller\AdminUserGroupController;
use GreyPanel\Controller\AuthController;
use GreyPanel\Controller\BalanceController;
use GreyPanel\Controller\BanController;
use GreyPanel\Controller\ChatController;
use GreyPanel\Controller\CronController;
use GreyPanel\Controller\ForumController;
use GreyPanel\Controller\HomeController;
use GreyPanel\Controller\MonitorController;
use GreyPanel\Controller\NewsController;
use GreyPanel\Controller\OnlineController;
use GreyPanel\Controller\PaymentController;
use GreyPanel\Controller\SitemapController;
use GreyPanel\Controller\UserController;
use GreyPanel\Core\Container;
use GreyPanel\Core\Database;
use GreyPanel\Interface\Repository\ChatRepositoryInterface;
use GreyPanel\Interface\Repository\ForumCategoryRepositoryInterface;
use GreyPanel\Interface\Repository\ForumForumRepositoryInterface;
use GreyPanel\Interface\Repository\ForumLikeRepositoryInterface;
use GreyPanel\Interface\Repository\ForumPostRepositoryInterface;
use GreyPanel\Interface\Repository\ForumReadRepositoryInterface;
use GreyPanel\Interface\Repository\ForumThreadRepositoryInterface;
use GreyPanel\Interface\Repository\LogRepositoryInterface;
use GreyPanel\Interface\Repository\MoneyLogRepositoryInterface;
use GreyPanel\Interface\Repository\MonitorServerRepositoryInterface;
use GreyPanel\Interface\Repository\NewsRepositoryInterface;
use GreyPanel\Interface\Repository\OnlineRepositoryInterface;
use GreyPanel\Interface\Repository\PaymentRepositoryInterface;
use GreyPanel\Interface\Repository\UserRepositoryInterface;
use GreyPanel\Interface\Service\AuthServiceInterface;
use GreyPanel\Interface\Service\AvatarServiceInterface;
use GreyPanel\Interface\Service\BanServiceInterface;
use GreyPanel\Interface\Service\ChatServiceInterface;
use GreyPanel\Interface\Service\CronServiceInterface;
use GreyPanel\Interface\Service\EncryptionServiceInterface;
use GreyPanel\Interface\Service\ForumServiceInterface;
use GreyPanel\Interface\Service\MarkdownServiceInterface;
use GreyPanel\Interface\Service\ModuleServiceInterface;
use GreyPanel\Interface\Service\MonitorServiceInterface;
use GreyPanel\Interface\Service\NewsServiceInterface;
use GreyPanel\Interface\Service\SeoServiceInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Interface\Service\SettingsServiceInterface;
use GreyPanel\Interface\Service\ThemeServiceInterface;
use GreyPanel\Middleware\CsrfMiddleware;
use GreyPanel\Middleware\RateLimitMiddleware;
use GreyPanel\Repository\ChatRepository;
use GreyPanel\Repository\ForumCategoryRepository;
use GreyPanel\Repository\ForumForumRepository;
use GreyPanel\Repository\ForumLikeRepository;
use GreyPanel\Repository\ForumPostRepository;
use GreyPanel\Repository\ForumReadRepository;
use GreyPanel\Repository\ForumThreadRepository;
use GreyPanel\Repository\LogRepository;
use GreyPanel\Repository\MoneyLogRepository;
use GreyPanel\Repository\MonitorServerRepository;
use GreyPanel\Repository\NewsRepository;
use GreyPanel\Repository\OnlineRepository;
use GreyPanel\Repository\PaymentRepository;
use GreyPanel\Repository\ServiceRepository;
use GreyPanel\Repository\ServiceServerRepository;
use GreyPanel\Repository\TariffRepository;
use GreyPanel\Repository\UserGroupRepository;
use GreyPanel\Repository\UserRepository;
use GreyPanel\Repository\UserServiceRepository;
use GreyPanel\Service\AuthService;
use GreyPanel\Service\AvatarService;
use GreyPanel\Service\BanService;
use GreyPanel\Service\ChatService;
use GreyPanel\Service\CronService;
use GreyPanel\Service\EncryptionService;
use GreyPanel\Service\ForumService;
use GreyPanel\Service\MarkdownService;
use GreyPanel\Service\ModuleService;
use GreyPanel\Service\MonitorService;
use GreyPanel\Service\NewsService;
use GreyPanel\Service\PermissionService;
use GreyPanel\Service\RecaptchaService;
use GreyPanel\Service\SeoService;
use GreyPanel\Service\ServiceActivationService;
use GreyPanel\Service\SessionService;
use GreyPanel\Service\SettingsService;
use GreyPanel\Service\SiteService;
use GreyPanel\Service\ThemeService;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

return function (Container $container) {
    $container->singleton(Database::class, function () {
        return new Database([
            'DB_HOST' => $_ENV['DB_HOST'],
            'DB_NAME' => $_ENV['DB_NAME'],
            'DB_USER' => $_ENV['DB_USER'],
            'DB_PASS' => $_ENV['DB_PASS'],
            'DB_CHARSET' => $_ENV['DB_CHARSET'],
            'DB_PREFIX' => $_ENV['DB_PREFIX'],
        ]);
    });

    // Репозитории
    $container->bind(UserRepositoryInterface::class, function ($c) {
        return new UserRepository(
            $c->get(Database::class),
            $c->get(UserGroupRepository::class)
        );
    });
    $container->singleton(UserGroupRepository::class);
    $container->bind(LogRepositoryInterface::class, LogRepository::class);
    $container->bind(MoneyLogRepositoryInterface::class, MoneyLogRepository::class);
    $container->bind(BanServiceInterface::class, BanService::class);
    $container->bind(MonitorServerRepositoryInterface::class, MonitorServerRepository::class);
    $container->bind(ForumCategoryRepositoryInterface::class, ForumCategoryRepository::class);
    $container->bind(ForumForumRepositoryInterface::class, ForumForumRepository::class);
    $container->bind(ForumThreadRepositoryInterface::class, ForumThreadRepository::class);
    $container->bind(ForumPostRepositoryInterface::class, ForumPostRepository::class);
    $container->bind(ForumLikeRepositoryInterface::class, ForumLikeRepository::class);
    $container->bind(ForumReadRepositoryInterface::class, ForumReadRepository::class);
    $container->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
    $container->bind(OnlineRepositoryInterface::class, OnlineRepository::class);
    $container->bind(ChatRepositoryInterface::class, ChatRepository::class);
    $container->bind(NewsRepositoryInterface::class, NewsRepository::class);

    // Новые репозитории для услуг и тарифов
    $container->singleton(ServiceRepository::class);
    $container->singleton(TariffRepository::class);
    $container->singleton(UserServiceRepository::class);
    $container->singleton(ServiceServerRepository::class);

    // Сервисы
    $container->bind(AuthServiceInterface::class, AuthService::class);
    $container->bind(MonitorServiceInterface::class, MonitorService::class);
    $container->singleton(SettingsServiceInterface::class, SettingsService::class);
    $container->singleton(EncryptionServiceInterface::class, function ($c) {
        return new EncryptionService($_ENV['ENCRYPTION_KEY']);
    });
    $container->bind(ForumServiceInterface::class, ForumService::class);
    $container->bind(ThemeServiceInterface::class, ThemeService::class);
    $container->bind(AvatarServiceInterface::class, AvatarService::class);
    $container->bind(MarkdownServiceInterface::class, MarkdownService::class);
    $container->bind(CronServiceInterface::class, CronService::class);
    $container->bind(SessionServiceInterface::class, SessionService::class);
    $container->bind(CsrfMiddleware::class, function ($c) {
        return new CsrfMiddleware($c->get(SessionServiceInterface::class));
    });
    $container->singleton(ModuleServiceInterface::class, ModuleService::class);
    $container->bind(SeoServiceInterface::class, function ($c) {
        return new SeoService(
            $c->get(Database::class),
            $c->get(SettingsService::class),
            $c->get(SiteService::class)
        );
    });
    $container->bind(ChatServiceInterface::class, ChatService::class);
    $container->bind(NewsServiceInterface::class, NewsService::class);
    $container->singleton(RecaptchaService::class);
    $container->singleton(SiteService::class);

    $container->singleton(PermissionService::class, function ($c) {
        return new PermissionService($c->get(UserGroupRepository::class));
    });

    $container->singleton(ServiceActivationService::class, function ($c) {
        return new ServiceActivationService(
            $c->get(MonitorServerRepositoryInterface::class),
            $c->get(ServiceServerRepository::class),
            $c->get(EncryptionServiceInterface::class),
            $c->get(UserServiceRepository::class),
            $c->get(LoggerInterface::class),
            $c->get(UserRepository::class),
            $c->get(UserGroupRepository::class)
        );
    });
    $container->bind(\GreyPanel\Service\StatisticsService::class);

    // Контроллеры
    $container->bind(UserController::class);
    $container->bind(AuthController::class);
    $container->bind(HomeController::class);
    $container->bind(ForumController::class);
    $container->bind(AdminController::class, function ($c) {
        return new AdminController(
            $c->get(UserRepositoryInterface::class),
            $c->get(ForumThreadRepositoryInterface::class),
            $c->get(LogRepositoryInterface::class),
            $c->get(SettingsServiceInterface::class),
            $c->get(MoneyLogRepositoryInterface::class),
            $c->get(ThemeServiceInterface::class),
            $c->get(ForumForumRepositoryInterface::class),
            $c->get(OnlineRepositoryInterface::class),
            $c->get(SessionService::class),
            $c->get(EncryptionServiceInterface::class),
            $c->get(UserGroupRepository::class),
            $c->get(PermissionService::class)
        );
    });
    $container->bind(AdminUserGroupController::class);
    $container->bind(AdminServerSettingsController::class);
    $container->bind(AdminForumController::class);
    $container->bind(AdminModuleController::class);
    $container->bind(AdminSeoController::class);
    $container->bind(AdminNewsController::class);
    $container->bind(BanController::class);
    $container->bind(BalanceController::class);
    $container->bind(MonitorController::class);
    $container->bind(PaymentController::class);
    $container->bind(OnlineController::class);
    $container->bind(CronController::class);
    $container->bind(SitemapController::class);
    $container->bind(ChatController::class);
    $container->bind(NewsController::class);
    $container->bind(AdminServiceController::class);
    $container->bind(StatisticsController::class);

    $container->bind(CronCommand::class);

    // Логгер
    $container->singleton(LoggerInterface::class, function () {
        $logger = new Logger('grey');
        $logger->pushHandler(new RotatingFileHandler(
            __DIR__ . '/../var/logs/app.log',
            30,
            Logger::DEBUG
        ));
        return $logger;
    });

    // Rate Limiter
    $rateLimiterConfig = require __DIR__ . '/rate_limiter.php';
    foreach ($rateLimiterConfig as $key => $config) {
        $container->singleton('rate_limit.' . $key, function () use ($config, $key) {
            return new RateLimitMiddleware($key, $config);
        });
    }
};
