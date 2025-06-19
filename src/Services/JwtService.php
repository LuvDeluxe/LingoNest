<?php

namespace App\Services;

// Import JWT lib
use Firebase\JWT\JWT;
use mysql_xdevapi\Exception;

class JwtService
{
  private string $key;

  public function __construct()
  {
    // Get secret key from ENV var
    $this->key = $_ENV["JWT_SECRET"];
  }

  /**
   * Generate a JSON Web Token for a given user ID.
   * @param int $userId The id of the user
   * @return string The generated JWT
   */
  public function generate(int $userId): string
  {
    $issuedAt = time();
    // Valid for 1 h
    $expirationTime = $issuedAt + 3600;

    $payload = [
      "iat" => $issuedAt,
      "exp" => $expirationTime,
      "sub" => $userId
    ];

    return JWT::encode($payload, $this->key, "HS256");
  }

  /*
   * Validates a JWT and returns the user ID if valid
   * @param string|null $token The JWT string
   * @return int|null The user id from the token or null if invalid
   */
  public function validate(?string $token): ?int
  {
    if ($token === null) {
      return null;
    }

    try {
      $decoded = JWT::decode($token, new Key($this->key, "HS256"));

      //return the user ID
      return $decoded->sub;
    } catch (Exception $e) {
      return null;
    }
  }
}