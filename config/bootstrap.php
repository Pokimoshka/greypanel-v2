<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use GreyPanel\Core\Container;

if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', realpath(__DIR__ . '/..'));
}

require ROOT_DIR . '/vendor/autoload.php';

$envFile = ROOT_DIR . '/.env';
$installed = false;

if (file_exists($envFile)) {
    $dotenv = Dotenv::createImmutable(ROOT_DIR);
    try {
        $dotenv->load();
        $installed = ($_ENV['APP_INSTALLED'] ?? 'false') === 'true';
    } catch (\Exception $e) {
        $installed = false;
    }
}

$container = new Container();

if ($installed) {
    $services = require __DIR__ . '/services.php';
    $services($container);
}

define('APP_INSTALLED', $installed);

return $container;
