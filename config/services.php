<?php
declare(strict_types=1);

use GreyPanel\Core\Container;
use GreyPanel\Core\Database;
use GreyPanel\Repository\UserRepository;
use GreyPanel\Repository\UserRepositoryInterface;
use GreyPanel\Repository\LogRepository;
use GreyPanel\Repository\LogRepositoryInterface;
use GreyPanel\Repository\MoneyLogRepository;
use GreyPanel\Repository\MoneyLogRepositoryInterface;
use GreyPanel\Repository\VipServerRepository;
use GreyPanel\Repository\VipServerRepositoryInterface;
use GreyPanel\Repository\VipPrivilegeRepository;
use GreyPanel\Repository\VipPrivilegeRepositoryInterface;
use GreyPanel\Repository\VipUserRepository;
use GreyPanel\Repository\VipUserRepositoryInterface;
use GreyPanel\Service\BanService;
use GreyPanel\Service\BanServiceInterface;
use GreyPanel\Repository\MonitorServerRepository;
use GreyPanel\Repository\MonitorServerRepositoryInterface;
use GreyPanel\Repository\ForumCategoryRepository;
use GreyPanel\Repository\ForumCategoryRepositoryInterface;
use GreyPanel\Repository\ForumForumRepository;
use GreyPanel\Repository\ForumForumRepositoryInterface;
use GreyPanel\Repository\ForumThreadRepository;
use GreyPanel\Repository\ForumThreadRepositoryInterface;
use GreyPanel\Repository\ForumPostRepository;
use GreyPanel\Repository\ForumPostRepositoryInterface;
use GreyPanel\Repository\ForumLikeRepository;
use GreyPanel\Repository\ForumLikeRepositoryInterface;
use GreyPanel\Repository\ForumReadRepository;
use GreyPanel\Repository\ForumReadRepositoryInterface;
use GreyPanel\Repository\PaymentRepository;
use GreyPanel\Repository\PaymentRepositoryInterface;
use GreyPanel\Repository\OnlineRepository;
use GreyPanel\Repository\OnlineRepositoryInterface;
use GreyPanel\Service\AuthService;
use GreyPanel\Service\AuthServiceInterface;
use GreyPanel\Service\SettingsService;
use GreyPanel\Service\SettingsServiceInterface;
use GreyPanel\Service\EncryptionService;
use GreyPanel\Service\EncryptionServiceInterface;
use GreyPanel\Service\VipActivationService;
use GreyPanel\Service\VipActivationServiceInterface;
use GreyPanel\Service\MonitorService;
use GreyPanel\Service\MonitorServiceInterface;
use GreyPanel\Service\MarkdownService;
use GreyPanel\Service\MarkdownServiceInterface;
use GreyPanel\Service\ForumService;
use GreyPanel\Service\ForumServiceInterface;
use GreyPanel\Service\ThemeService;
use GreyPanel\Service\ThemeServiceInterface;
use GreyPanel\Service\AvatarService;
use GreyPanel\Service\AvatarServiceInterface;
use GreyPanel\Service\CronService;
use GreyPanel\Service\CronServiceInterface;
use GreyPanel\Service\SessionService;
use GreyPanel\Service\SessionServiceInterface;
use GreyPanel\Service\ModuleService;
use GreyPanel\Service\ModuleServiceInterface;
use GreyPanel\Service\SeoService;
use GreyPanel\Service\SeoServiceInterface;
use GreyPanel\Controller\UserController;
use GreyPanel\Controller\AuthController;
use GreyPanel\Controller\HomeController;
use GreyPanel\Controller\ForumController;
use GreyPanel\Controller\AdminController;
use GreyPanel\Controller\AdminVipController;
use GreyPanel\Controller\AdminServerSettingsController;
use GreyPanel\Controller\AdminForumController;
use GreyPanel\Controller\BanController;
use GreyPanel\Controller\BalanceController;
use GreyPanel\Controller\MonitorController;
use GreyPanel\Controller\PaymentController;
use GreyPanel\Controller\VipController;
use GreyPanel\Controller\OnlineController;
use GreyPanel\Controller\CronController;
use GreyPanel\Controller\AdminModuleController;
use GreyPanel\Controller\AdminSeoController;
use GreyPanel\Controller\SitemapController;
use GreyPanel\Repository\ChatRepository;
use GreyPanel\Repository\ChatRepositoryInterface;
use GreyPanel\Service\ChatService;
use GreyPanel\Service\ChatServiceInterface;
use GreyPanel\Controller\ChatController;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Psr\Log\LoggerInterface;
use GreyPanel\Middleware\CsrfMiddleware;
use GreyPanel\Command\CronCommand;
use GreyPanel\Repository\NewsRepository;
use GreyPanel\Repository\NewsRepositoryInterface;
use GreyPanel\Service\NewsService;
use GreyPanel\Service\NewsServiceInterface;
use GreyPanel\Controller\NewsController;
use GreyPanel\Controller\AdminNewsController;

return function (Container $container) {
    $container->singleton(Database::class, function () {
        return new Database($_ENV);
    });

    $container->bind(UserRepositoryInterface::class, UserRepository::class);
    $container->bind(LogRepositoryInterface::class, LogRepository::class);
    $container->bind(MoneyLogRepositoryInterface::class, MoneyLogRepository::class);
    $container->bind(VipServerRepositoryInterface::class, VipServerRepository::class);
    $container->bind(VipPrivilegeRepositoryInterface::class, VipPrivilegeRepository::class);
    $container->bind(VipUserRepositoryInterface::class, VipUserRepository::class);
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

    $container->bind(AuthServiceInterface::class, AuthService::class);
    $container->bind(MonitorServiceInterface::class, MonitorService::class);
    $container->singleton(SettingsServiceInterface::class, SettingsService::class);
    $container->singleton(EncryptionServiceInterface::class, function ($c) {
        return new EncryptionService($_ENV['ENCRYPTION_KEY']);
    });
    $container->bind(VipActivationServiceInterface::class, VipActivationService::class);
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
    $container->bind(SeoServiceInterface::class, SeoService::class);
    $container->bind(ChatServiceInterface::class, ChatService::class);
    $container->bind(NewsServiceInterface::class, NewsService::class);

    $container->bind(UserController::class);
    $container->bind(AuthController::class);
    $container->bind(HomeController::class);
    $container->bind(ForumController::class);
    $container->bind(AdminController::class);
    $container->bind(AdminVipController::class);
    $container->bind(AdminServerSettingsController::class);
    $container->bind(AdminForumController::class);
    $container->bind(BanController::class);
    $container->bind(BalanceController::class);
    $container->bind(MonitorController::class);
    $container->bind(PaymentController::class);
    $container->bind(VipController::class);
    $container->bind(OnlineController::class);
    $container->bind(CronController::class);
    $container->bind(AdminModuleController::class);
    $container->bind(AdminSeoController::class);
    $container->bind(SitemapController::class);
    $container->bind(ChatController::class);
    $container->bind(NewsController::class);
    $container->bind(AdminNewsController::class);

    $container->bind(CronCommand::class);

    $container->singleton(LoggerInterface::class, function () {
        $logger = new Logger('grey');
        $logger->pushHandler(new RotatingFileHandler(
            __DIR__ . '/../var/logs/app.log',
            30,
            Logger::DEBUG
        ));
        return $logger;
    });
};