<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

set_error_handler("App\\ErrorHandler::handleError");
set_exception_handler("App\\ErrorHandler::handleException");

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

header("Content-type: application/json; charset=UTF-8");

$database = new App\Database(
  $_ENV['DB_HOST'],
  $_ENV['DB_NAME'],
  $_ENV['DB_USER'],
  $_ENV['DB_PASSWORD']
);

$router = new App\Router();

$user_controller = new App\Controllers\UserController($database);
$router->add("POST", "/api/register", [$user_controller, "register"]);

$router->dispatch();