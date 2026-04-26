<?php

declare(strict_types=1);

define('ROOT_DIR', dirname(__DIR__));
define('ENV_FILE', ROOT_DIR . '/.env');
define('INSTALL_DIR', __DIR__ . '/install');   // public/install

// ---- 1. Проверка состояния установки ----
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$isInstalled = false;

if (file_exists(ENV_FILE)) {
    $envContent = file_get_contents(ENV_FILE);
    $isInstalled = strpos($envContent, 'APP_INSTALLED=true') !== false;
}

// Если сайт не установлен и есть папка install – редиректим (кроме самого /install)
if (!$isInstalled && is_dir(INSTALL_DIR) && file_exists(INSTALL_DIR . '/index.php') && strpos($requestUri, '/install') !== 0) {
    header('Location: /install/');
    exit;
}

// Если сайт не установлен, а установщика нет – сообщение
if (!$isInstalled && (!is_dir(INSTALL_DIR) || !file_exists(INSTALL_DIR . '/index.php'))) {
    http_response_code(503);
    die('Сайт не установлен, а папка install отсутствует. Загрузите установщик.');
}

// Если запрос к /install, но сайт уже установлен – 404
if (strpos($requestUri, '/install') === 0 && $isInstalled) {
    http_response_code(404);
    die('Установка уже завершена. Удалите папку install.');
}

// ---- 2. Загрузка автозагрузчика и .env ----
require ROOT_DIR . '/vendor/autoload.php';

use Dotenv\Dotenv;
use GreyPanel\Core\App;
use GreyPanel\Core\Container;
use GreyPanel\Core\Router;
use GreyPanel\Interface\Repository\OnlineRepositoryInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Interface\Service\SettingsServiceInterface;

$dotenv = Dotenv::createImmutable(ROOT_DIR);
$dotenv->load();

define('APP_ENV', $_ENV['APP_ENV'] ?? 'prod');

// ---- 3. Создание контейнера и загрузка сервисов ----
$container = new Container();
$services = require ROOT_DIR . '/config/services.php';
$services($container);

// ---- 4. Получение настроек ----
/** @var SettingsServiceInterface $settings */
$settings = $container->get(SettingsServiceInterface::class);

// ---- 5. Режим отладки ----
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

// ---- 6. Сессия ----
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

// ---- 7. Запуск приложения ----
$router = new Router($container);
$router->load(ROOT_DIR . '/config/routes.php');
$app = new App(
    $router,
    $container,
    $container->get(OnlineRepositoryInterface::class),
    $container->get(SessionServiceInterface::class)
);
$app->run();
