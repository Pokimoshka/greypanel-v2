<?php

declare(strict_types=1);

define('ROOT_DIR', dirname(__DIR__));
define('ENV_FILE', ROOT_DIR . '/.env');
define('INSTALL_DIR', __DIR__ . '/install');

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$isInstalled = false;

if (file_exists(ENV_FILE)) {
    $envContent = file_get_contents(ENV_FILE);
    $isInstalled = strpos($envContent, 'APP_INSTALLED=true') !== false;
}

if (!$isInstalled && is_dir(INSTALL_DIR) && file_exists(INSTALL_DIR . '/index.php') && !str_starts_with($requestUri, '/install')) {
    header('Location: /install/');
    exit;
}

if (!$isInstalled && (!is_dir(INSTALL_DIR) || !file_exists(INSTALL_DIR . '/index.php'))) {
    http_response_code(503);
    die('Сайт не установлен, а папка install отсутствует. Загрузите установщик.');
}

if (str_starts_with($requestUri, '/install') && $isInstalled) {
    http_response_code(404);
    die('Установка уже завершена. Удалите папку install.');
}

require ROOT_DIR . '/vendor/autoload.php';

use Dotenv\Dotenv;
use GreyPanel\Core\App;
use GreyPanel\Core\Container;
use GreyPanel\Interface\Repository\OnlineRepositoryInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Interface\Service\SettingsServiceInterface;
use Symfony\Component\Routing\RouteCollection;

$dotenv = Dotenv::createImmutable(ROOT_DIR);
$dotenv->load();
define('APP_ENV', $_ENV['APP_ENV'] ?? 'prod');

$container = new Container();
$services = require ROOT_DIR . '/config/services.php';
$services($container);

$routeCollection = new RouteCollection();
$routesLoader = require ROOT_DIR . '/config/routes.php';
$routesLoader($routeCollection);

$settings = $container->get(SettingsServiceInterface::class);

$appDebug = $settings->getBool('app_debug', false);
if ($appDebug) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}
define('APP_DEBUG', $appDebug);

$sessionName = $settings->get('session_name', 'greysession');
$sessionLifetime = $settings->getInt('session_lifetime', 7200);
$cookieParams = [
    'lifetime' => $sessionLifetime,
    'path' => '/',
    'domain' => '',
    'secure' => ($_ENV['APP_ENV'] ?? 'dev') === 'prod',
    'httponly' => true,
    'samesite' => 'Lax',
];
session_set_cookie_params($cookieParams);
ini_set('session.cookie_lifetime', $sessionLifetime);
ini_set('session.gc_maxlifetime', $sessionLifetime);
session_name($sessionName);
session_start();

$app = new App(
    $routeCollection,
    $container,
    $container->get(OnlineRepositoryInterface::class),
    $container->get(SessionServiceInterface::class)
);
$app->run();
