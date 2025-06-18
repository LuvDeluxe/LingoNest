<?php

// Enable strict types
declare(strict_types=1);

// Load composers autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// Setup global error / exception handlers
set_error_handler("App\\ErrorHandler::handleError");
set_exception_handler("App\\ErrorHandler::handleException");

// Load Environmental variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Set default Content-Type header
header("Content-type: application/json; charset=UTF-8");

// Initialize Core Services
$database = new App\Database(
  $_ENV['DB_HOST'],
  $_ENV['DB_NAME'],
  $_ENV['DB_USER'],
  $_ENV['DB_PASSWORD']
);

$router = new App\Router();

// Define API routes
$user_controller = new App\Controllers\UserController($database);

// Registration Route
$router->add("POST", "/api/register", [$user_controller, "register"]);

// Login route
$router->add("POST", "/api/login", [$user_controller, "login"]);

// Dispatch the request
$router->dispatch();