<?php
if (strpos($_SERVER['REQUEST_URI'], '/install/') === 0) {
    if (file_exists(__DIR__ . '/../install/index.php')) {
        include __DIR__ . '/../install/index.php';
        exit;
    } else {
        die('Установщик не найден. Загрузите папку /install.');
    }
}

if (!file_exists(__DIR__ . '/../.env')) {
    header('Location: /install/');
    exit;
}

if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff2?|ttf|eot)$/', $_SERVER['REQUEST_URI'])) {
    return false;
}

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use GreyPanel\Core\App;
use GreyPanel\Core\Router;
use GreyPanel\Core\Container;
use GreyPanel\Repository\OnlineRepositoryInterface;
use GreyPanel\Service\SessionServiceInterface;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

define('APP_ENV', $_ENV['APP_ENV'] ?? 'prod');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

$container = new Container();
$services = require __DIR__ . '/../config/services.php';
$services($container);

$router = new Router($container);
$router->load(__DIR__ . '/../config/routes.php');
$app = new App($router, $container, $container->get(OnlineRepositoryInterface::class), $container->get(SessionServiceInterface::class));
$app->run();
exit;