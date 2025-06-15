<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/../../vendor/autoload.php';

// Set default environment variables for testing
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = 1;

if (file_exists(dirname(__DIR__) . '/.env') && method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}