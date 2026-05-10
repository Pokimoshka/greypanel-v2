<?php

declare(strict_types=1);

use GreyPanel\Core\Config;
use GreyPanel\Core\Container;
use GreyPanel\Core\Database;
use GreyPanel\Interface\Repository\OnlineRepositoryInterface;
use GreyPanel\Interface\Repository\UserRepositoryInterface;
use GreyPanel\Interface\Service\EncryptionServiceInterface;
use GreyPanel\Interface\Service\PermissionServiceInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Interface\Service\SettingsServiceInterface;
use GreyPanel\Interface\Service\ThemeServiceInterface;
use GreyPanel\Middleware\LocaleMiddleware;
use GreyPanel\Middleware\RateLimitMiddleware;
use GreyPanel\Service\AuthService;
use GreyPanel\Service\AvatarService;
use GreyPanel\Service\BanService;
use GreyPanel\Service\CacheService;
use GreyPanel\Service\ChatService;
use GreyPanel\Service\CronService;
use GreyPanel\Service\EncryptionService;
use GreyPanel\Service\ForumService;
use GreyPanel\Service\ImageUploadService;
use GreyPanel\Service\LocaleManager;
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
use GreyPanel\Service\StatisticsService;
use GreyPanel\Service\ThemeService;
use GreyPanel\Validator\Constraints\UniqueEmailValidator;
use GreyPanel\Validator\Constraints\UniqueUsernameValidator;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

return function (Container $container) {
    $container->singleton(Config::class, function () {
        return new Config($_ENV);
    });

    $container->singleton(Database::class, function (Container $c) {
        $config = $c->get(Config::class);
        return new Database([
            'DB_HOST'    => $config->get('DB_HOST'),
            'DB_NAME'    => $config->get('DB_NAME'),
            'DB_USER'    => $config->get('DB_USER'),
            'DB_PASS'    => $config->get('DB_PASS'),
            'DB_CHARSET' => $config->get('DB_CHARSET'),
            'DB_PREFIX'  => $config->get('DB_PREFIX'),
        ]);
    });

    $container->singleton(SessionServiceInterface::class, function (Container $c) {
        return new SessionService(
            $c->get(UserRepositoryInterface::class),
            $c->get(SettingsServiceInterface::class)
        );
    });

    $container->singleton(PermissionServiceInterface::class, function (Container $c) {
        return new PermissionService($c->get(SessionServiceInterface::class));
    });

    $container->singleton(PermissionService::class, function (Container $c) {
        return $c->get(PermissionServiceInterface::class);
    });

    $container->singleton(EncryptionService::class, function (Container $c) {
        $config = $c->get(Config::class);
        return new EncryptionService($config->get('ENCRYPTION_KEY'));
    });
    $container->singleton(EncryptionServiceInterface::class, function (Container $c) {
        return $c->get(EncryptionService::class);
    });

    $container->singleton(LoggerInterface::class, function () {
        $logger = new Logger('grey');
        $logger->pushHandler(new RotatingFileHandler(
            ROOT_DIR . '/var/logs/app.log',
            30,
            Logger::DEBUG
        ));
        return $logger;
    });

    $container->singleton(TranslatorInterface::class, function () {
        $translator = new Translator('ru');
        $translator->addLoader('yaml', new YamlFileLoader());
        $transDir = ROOT_DIR . '/resources/translations/';
        foreach (glob($transDir . 'messages.*.yaml') as $file) {
            if (preg_match('/messages\.(\w+)\.yaml$/', basename($file), $matches)) {
                $translator->addResource('yaml', $file, $matches[1]);
            }
        }
        foreach (glob($transDir . 'validators.*.yaml') as $file) {
            if (preg_match('/validators\.(\w+)\.yaml$/', basename($file), $matches)) {
                $translator->addResource('yaml', $file, $matches[1], 'validators');
            }
        }
        return $translator;
    });

    $container->singleton(Translator::class, function (Container $c) {
        return $c->get(TranslatorInterface::class);
    });

    $container->singleton(LocaleManager::class, function () {
        $transDir = ROOT_DIR . '/resources/translations/';
        $locales = [];
        foreach (glob($transDir . 'messages.*.yaml') as $file) {
            if (preg_match('/messages\.(\w+)\.yaml$/', basename($file), $matches)) {
                $locales[] = $matches[1];
            }
        }
        return new LocaleManager($locales);
    });

    $container->singleton(LocaleMiddleware::class, function (Container $c) {
        return new LocaleMiddleware(
            $c->get(TranslatorInterface::class),
            $c->get(SessionServiceInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(SettingsServiceInterface::class),
            $c->get(LocaleManager::class)
        );
    });

    $rateLimiterConfig = require __DIR__ . '/rate_limiter.php';
    foreach ($rateLimiterConfig as $key => $configItem) {
        $container->singleton('rate_limit.' . $key, function () use ($configItem, $key, $container) {
            return new RateLimitMiddleware(
                $key,
                $configItem,
                $container->get(SessionServiceInterface::class)
            );
        });
    }

    $container->bind(UniqueUsernameValidator::class);
    $container->bind('unique.username', UniqueUsernameValidator::class);
    $container->bind(UniqueEmailValidator::class);
    $container->bind('unique.email', UniqueEmailValidator::class);

    $container->singleton(ConstraintValidatorFactoryInterface::class, function (Container $c) {
        return new class ($c) implements ConstraintValidatorFactoryInterface {
            public function __construct(private Container $container)
            {
            }
            public function getInstance(Constraint $constraint): ConstraintValidatorInterface
            {
                $className = $constraint->validatedBy();
                if (class_exists($className)) {
                    return $this->container->get($className);
                }
                return $this->container->get($className);
            }
        };
    });

    $container->singleton(ValidatorInterface::class, function (Container $c) {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->setTranslationDomain('validators')
            ->setConstraintValidatorFactory($c->get(ConstraintValidatorFactoryInterface::class))
            ->getValidator();
    });

    $container->singleton(HtmlSanitizer::class, function () {
        $config = (new HtmlSanitizerConfig())
            ->allowSafeElements()
            ->allowStaticElements()
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            ->blockElement('script')
            ->blockElement('style')
            ->blockElement('iframe')
            ->blockElement('object')
            ->blockElement('embed')
            ->blockElement('form')
            ->blockElement('input')
            ->blockElement('button');
        return new HtmlSanitizer($config);
    });

    $container->singleton(LockFactory::class, function () {
        return new LockFactory(new FlockStore(ROOT_DIR . '/var/lock'));
    });

    $container->singleton(SerializerInterface::class, function () {
        return new Serializer(
            [new DateTimeNormalizer(), new ObjectNormalizer()],
            [new JsonEncoder()]
        );
    });

    $container->singleton(SettingsServiceInterface::class, function (Container $c) {
        return new SettingsService($c->get(Database::class));
    });

    $container->singleton(ThemeService::class, function (Container $c) {
        return new ThemeService($c->get(Database::class));
    });

    $container->singleton(ImageUploadService::class);
    $container->singleton(ModuleService::class, function (Container $c) {
        return new ModuleService($c->get(Database::class));
    });
    $container->singleton(SiteService::class, function (Container $c) {
        return new SiteService($c->get(SettingsServiceInterface::class));
    });
    $container->singleton(SeoService::class, function (Container $c) {
        return new SeoService(
            $c->get(Database::class),
            $c->get(SettingsServiceInterface::class),
            $c->get(SiteService::class)
        );
    });
    $container->singleton(CronService::class, function (Container $c) {
        return new CronService(
            $c->get(\GreyPanel\Repository\UserServiceRepository::class),
            $c->get(MonitorService::class),
            $c->get(SettingsServiceInterface::class),
            $c->get(SeoService::class),
            $c->get(OnlineRepositoryInterface::class),
            $c->get(LockFactory::class)
        );
    });

    $container->singleton(MonitorService::class, function (Container $c) {
        return new MonitorService(
            $c->get(\GreyPanel\Repository\MonitorServerRepository::class)
        );
    });

    $container->singleton(StatisticsService::class, function (Container $c) {
        return new StatisticsService(
            $c->get(\GreyPanel\Repository\MonitorServerRepository::class),
            $c->get(EncryptionServiceInterface::class)
        );
    });

    $container->singleton(BanService::class, function (Container $c) {
        return new BanService(
            $c->get(\GreyPanel\Repository\MonitorServerRepository::class),
            $c->get(EncryptionServiceInterface::class)
        );
    });

    $container->singleton(AuthService::class, function (Container $c) {
        return new AuthService(
            $c->get(UserRepositoryInterface::class),
            $c->get(\GreyPanel\Repository\UserGroupRepository::class),
            $c->get(TranslatorInterface::class),
            $c->get(ValidatorInterface::class),
            $c->get(PermissionServiceInterface::class)
        );
    });

    $container->singleton(\GreyPanel\Interface\Service\AuthServiceInterface::class, function (Container $c) {
        return $c->get(AuthService::class);
    });

    $container->singleton(ImageManager::class, function () {
        return new ImageManager(Driver::class);
    });

    $container->singleton(AvatarService::class, function (Container $c) {
        return new AvatarService(
            $c->get(ImageManager::class),
            $c->get(ValidatorInterface::class),
            $c->get(TranslatorInterface::class),
        );
    });

    $container->singleton(ChatService::class, function (Container $c) {
        return new ChatService(
            $c->get(\GreyPanel\Repository\ChatRepository::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(MarkdownService::class),
            $c->get(HtmlSanitizer::class)
        );
    });

    $container->singleton(MarkdownService::class, function (Container $c) {
        return new MarkdownService($c->get(HtmlSanitizer::class));
    });

    $container->singleton(ForumService::class, function (Container $c) {
        return new ForumService(
            $c->get(\GreyPanel\Repository\ForumCategoryRepository::class),
            $c->get(\GreyPanel\Repository\ForumForumRepository::class),
            $c->get(\GreyPanel\Repository\ForumThreadRepository::class),
            $c->get(\GreyPanel\Repository\ForumPostRepository::class),
            $c->get(\GreyPanel\Repository\ForumLikeRepository::class),
            $c->get(\GreyPanel\Repository\ForumReadRepository::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(MarkdownService::class),
            $c->get(SessionServiceInterface::class),
            $c->get(Database::class),
            $c->get(LoggerInterface::class)
        );
    });

    $container->singleton(NewsService::class, function (Container $c) {
        return new NewsService(
            $c->get(\GreyPanel\Repository\NewsRepository::class),
            $c->get(MarkdownService::class)
        );
    });

    $container->singleton(RecaptchaService::class, function (Container $c) {
        return new RecaptchaService(
            $c->get(SettingsServiceInterface::class),
            $c->get(EncryptionServiceInterface::class)
        );
    });

    $container->singleton(ServiceActivationService::class, function (Container $c) {
        return new ServiceActivationService(
            $c->get(\GreyPanel\Repository\MonitorServerRepository::class),
            $c->get(\GreyPanel\Repository\ServiceServerRepository::class),
            $c->get(EncryptionServiceInterface::class),
            $c->get(\GreyPanel\Repository\UserServiceRepository::class),
            $c->get(LoggerInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(\GreyPanel\Repository\UserGroupRepository::class),
            $c->get(LockFactory::class)
        );
    });

    $container->singleton(CacheService::class);

    $container->singleton(\GreyPanel\Repository\ChatRepository::class, function (Container $c) {
        return new \GreyPanel\Repository\ChatRepository($c->get(Database::class));
    });
    $container->singleton(\GreyPanel\Repository\ForumCategoryRepository::class, function (Container $c) {
        return new \GreyPanel\Repository\ForumCategoryRepository($c->get(Database::class));
    });
    $container->singleton(\GreyPanel\Repository\ForumForumRepository::class, function (Container $c) {
        return new \GreyPanel\Repository\ForumForumRepository($c->get(Database::class));
    });
    $container->singleton(\GreyPanel\Repository\ForumThreadRepository::class, function (Container $c) {
        return new \GreyPanel\Repository\ForumThreadRepository($c->get(Database::class));
    });
    $container->singleton(\GreyPanel\Repository\ForumPostRepository::class, function (Container $c) {
        return new \GreyPanel\Repository\ForumPostRepository($c->get(Database::class));
    });
    $container->singleton(\GreyPanel\Repository\ForumLikeRepository::class, function (Container $c) {
        return new \GreyPanel\Repository\ForumLikeRepository($c->get(Database::class));
    });
    $container->singleton(\GreyPanel\Repository\ForumReadRepository::class, function (Container $c) {
        return new \GreyPanel\Repository\ForumReadRepository($c->get(Database::class));
    });
    $container->singleton(\GreyPanel\Repository\MonitorServerRepository::class, function (Container $c) {
        return new \GreyPanel\Repository\MonitorServerRepository($c->get(Database::class));
    });
    $container->singleton(\GreyPanel\Repository\NewsRepository::class, function (Container $c) {
        return new \GreyPanel\Repository\NewsRepository($c->get(Database::class));
    });
    $container->singleton(\GreyPanel\Repository\OnlineRepository::class, function (Container $c) {
        return new \GreyPanel\Repository\OnlineRepository($c->get(Database::class));
    });
    $container->singleton(\GreyPanel\Repository\PaymentRepository::class, function (Container $c) {
        return new \GreyPanel\Repository\PaymentRepository($c->get(Database::class));
    });
    $container->singleton(\GreyPanel\Repository\ServiceRepository::class, function (Container $c) {
        return new \GreyPanel\Repository\ServiceRepository($c->get(Database::class));
    });
    $container->singleton(\GreyPanel\Repository\ServiceServerRepository::class, function (Container $c) {
        return new \GreyPanel\Repository\ServiceServerRepository($c->get(Database::class));
    });
    $container->singleton(\GreyPanel\Repository\TariffRepository::class, function (Container $c) {
        return new \GreyPanel\Repository\TariffRepository($c->get(Database::class));
    });
    $container->singleton(\GreyPanel\Repository\UserGroupRepository::class, function (Container $c) {
        return new \GreyPanel\Repository\UserGroupRepository($c->get(Database::class));
    });
    $container->singleton(\GreyPanel\Repository\UserServiceRepository::class, function (Container $c) {
        return new \GreyPanel\Repository\UserServiceRepository($c->get(Database::class));
    });
    $container->singleton(UserRepositoryInterface::class, function (Container $c) {
        return new \GreyPanel\Repository\UserRepository(
            $c->get(Database::class),
            $c->get(\GreyPanel\Repository\UserGroupRepository::class)
        );
    });
    $container->singleton(OnlineRepositoryInterface::class, function (Container $c) {
        return new \GreyPanel\Repository\OnlineRepository($c->get(Database::class));
    });

    $container->singleton(\GreyPanel\Interface\Repository\ChatRepositoryInterface::class, function (Container $c) {
        return $c->get(\GreyPanel\Repository\ChatRepository::class);
    });
    $container->singleton(\GreyPanel\Interface\Repository\ForumCategoryRepositoryInterface::class, function (Container $c) {
        return $c->get(\GreyPanel\Repository\ForumCategoryRepository::class);
    });
    $container->singleton(\GreyPanel\Interface\Repository\ForumForumRepositoryInterface::class, function (Container $c) {
        return $c->get(\GreyPanel\Repository\ForumForumRepository::class);
    });
    $container->singleton(\GreyPanel\Interface\Repository\ForumThreadRepositoryInterface::class, function (Container $c) {
        return $c->get(\GreyPanel\Repository\ForumThreadRepository::class);
    });
    $container->singleton(\GreyPanel\Interface\Repository\ForumPostRepositoryInterface::class, function (Container $c) {
        return $c->get(\GreyPanel\Repository\ForumPostRepository::class);
    });
    $container->singleton(\GreyPanel\Interface\Repository\ForumLikeRepositoryInterface::class, function (Container $c) {
        return $c->get(\GreyPanel\Repository\ForumLikeRepository::class);
    });
    $container->singleton(\GreyPanel\Interface\Repository\ForumReadRepositoryInterface::class, function (Container $c) {
        return $c->get(\GreyPanel\Repository\ForumReadRepository::class);
    });
    $container->singleton(\GreyPanel\Interface\Repository\MonitorServerRepositoryInterface::class, function (Container $c) {
        return $c->get(\GreyPanel\Repository\MonitorServerRepository::class);
    });
    $container->singleton(\GreyPanel\Interface\Repository\NewsRepositoryInterface::class, function (Container $c) {
        return $c->get(\GreyPanel\Repository\NewsRepository::class);
    });
    $container->singleton(\GreyPanel\Interface\Repository\PaymentRepositoryInterface::class, function (Container $c) {
        return $c->get(\GreyPanel\Repository\PaymentRepository::class);
    });
    $container->singleton(\GreyPanel\Interface\Repository\LogRepositoryInterface::class, function (Container $c) {
        return $c->get(\GreyPanel\Repository\LogRepository::class);
    });
    $container->singleton(\GreyPanel\Interface\Repository\MoneyLogRepositoryInterface::class, function (Container $c) {
        return $c->get(\GreyPanel\Repository\MoneyLogRepository::class);
    });
    $container->singleton(\GreyPanel\Interface\Service\BanServiceInterface::class, function (Container $c) {
        return $c->get(BanService::class);
    });
    $container->singleton(\GreyPanel\Interface\Service\ChatServiceInterface::class, function (Container $c) {
        return $c->get(ChatService::class);
    });
    $container->singleton(\GreyPanel\Interface\Service\ForumServiceInterface::class, function (Container $c) {
        return $c->get(ForumService::class);
    });
    $container->singleton(\GreyPanel\Interface\Service\MarkdownServiceInterface::class, function (Container $c) {
        return $c->get(MarkdownService::class);
    });
    $container->singleton(\GreyPanel\Interface\Service\MonitorServiceInterface::class, function (Container $c) {
        return $c->get(MonitorService::class);
    });
    $container->singleton(\GreyPanel\Interface\Service\NewsServiceInterface::class, function (Container $c) {
        return $c->get(NewsService::class);
    });
    $container->singleton(\GreyPanel\Interface\Service\SeoServiceInterface::class, function (Container $c) {
        return $c->get(SeoService::class);
    });
    $container->singleton(ThemeServiceInterface::class, function (Container $c) {
        return $c->get(ThemeService::class);
    });
    $container->singleton(\GreyPanel\Interface\Service\ModuleServiceInterface::class, function (Container $c) {
        return $c->get(ModuleService::class);
    });
};
