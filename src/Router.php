<?php
namespace App;

class Router
{
  private array $routes = [];

  /**
   * This method adds a new route to our $routes array.
   *
   * @param string $method The HTTP method ('GET', 'POST', etc.)
   * @param string $path The URL path to match (e.g., '/api/register')
   * @param array $handler The controller and method to execute (e.g., [UserController::class, 'register'])
   */
  public function add(string $method, string $path, array $handler): void
  {
    $this->routes[] = [
      "method" => $method,
      "path" => $path,
      "handler" => $handler
    ];
  }

  /**
   * This method is the engine of the router. It finds the correct route and executes its handler.
   */
  public function dispatch(): void
  {
    $requestPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    foreach ($this->routes as $route) {
      if ($route["path"] === $requestPath && $route["method"] === $requestMethod) {
        call_user_func($route["handler"]);
        return;
      }
    }

    http_response_code(404);
    echo json_encode(["message" => "Endpoint not found"]);
  }
}