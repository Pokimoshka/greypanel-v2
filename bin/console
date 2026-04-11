#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use GreyPanel\Core\Container;
use GreyPanel\Core\Database;
use GreyPanel\Command\CronCommand;
use Symfony\Component\Console\Application;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$container = new Container();

$services = require __DIR__ . '/../config/services.php';
$services($container);

$app = new Application('GreyPanel', '3.0');

$app->add($container->get(CronCommand::class));

$app->run();