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
   * @param object|null $middleware (Optional) The middleware object to run before the handler. Must have a handle() method.
   */
  public function add(string $method, string $path, array $handler, ?object $middleware = null): void
  {
    $this->routes[] = [
      "method" => $method,
      "path" => $path,
      "handler" => $handler,
      "middleware" => $middleware
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
        $params = [];

        if ($route["middleware"] !== null) {
          $userId = $route["middleware"]->handle();
          $params[] = $userId;
        }
        call_user_func_array($route['handler'], $params);
        return;
      }
    }

    http_response_code(404);
    echo json_encode(["message" => "Endpoint not found"]);
  }
}