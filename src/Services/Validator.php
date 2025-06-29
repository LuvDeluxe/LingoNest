<?php

namespace App\Services;

class Validator
{
  /**
   * Validates the data for user registration.
   *
   * @param array $data The input data from the request.
   * @return array An array of error messages. If the array is empty, the data is valid.
   */

  public function validateRegistration(array $data): array
  {
    // Initialize the errors array
    $errors = [];

    // Verify if name has value
    if (empty($data["name"])) {
      $errors[] = "Name is required.";
    }

    // Verify if email has value
    if (empty($data["email"])) {
      $errors[] = "Email is required.";
    }

    // Verify if password has value
    if (empty($data["password"])) {
      $errors[] = "Password is required.";
    }

    // Email format verification
    if (isset($data["email"]) && !filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
       $errors[] = "Invalid email format.";
    }

    // Password validation
    if (isset($data["password"])) {
      if (strlen($data["password"]) < 5) {
        $errors[] = "Password must be at least 5 characters long.";
      }

    if (!preg_match('/[^a-zA-Z0-9]/', $data['password'])) {
      $errors[] = "Password must contain at least one symbol.";
    }
    }
    return $errors;
  }
}