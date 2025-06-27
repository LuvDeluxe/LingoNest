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

//Instantiate router
$router = new App\Router();

// Instantiate user controller
$user_controller = new App\Controllers\UserController($database);

// Instantiate language controller
$language_controller = new App\Controllers\LanguageController($database);

// Instantiate middleware
$middleware = new \App\Middleware\AuthMiddleware();
// Registration Route
$router->add("POST", "/api/register", [$user_controller, "register"]);

// Login route
$router->add("POST", "/api/login", [$user_controller, "login"]);

// Profile route
$router->add("GET", "/api/profile", [$user_controller, "getProfile"], $middleware);

// Language routes
$router->add("GET", "/api/languages", [$language_controller, "getAllLanguages"]);

// Protected route to add a language to users profile
$router->add("POST", "/api/user/languages", [$user_controller, "addUserLanguage"], $middleware);

// Delete route
$router->add("DELETE", "/api/user/languages", [$user_controller, "deleteUserLanguage"], $middleware);

// Matching endpoint
$router->add("GET", "/api/matches", [$user_controller, "getMatches"], $middleware);

// Dispatch the request
$router->dispatch();