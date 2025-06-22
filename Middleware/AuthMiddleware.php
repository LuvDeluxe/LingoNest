<?php

namespace App\Middleware;

use App\Services\JwtService;

class AuthMiddleware
{
  private JwtService $jwtService;

  public function __construct()
  {
    // The middleware needs the JwtService to validate tokens.
    $this->jwtService = new JwtService();
  }

  /*
   * Checks for a valid token.
   * On failure, it stops the script.
   * On success, it returns the user's ID.
   * @return int The user's ID from the token.
   */
  public function handle(): int
  {
    $authHeader = $_SERVER["HTTP_AUTHORIZATION"] ?? null;
    if ($authHeader === null || !preg_match("/^Bearer\s+(.*)$/", $authHeader, $matches)) {
      http_response_code(401);
      echo json_encode(["message" => "Authorization token not found or invalid format."]);
      exit;
    }

    $userId = $this->jwtService->validate($matches[1]);

    if ($userId === null) {
      http_response_code(401);
      echo json_encode(["message" => "Invalid / expired token."]);
      exit;
    }

    return $userId;
  }
}