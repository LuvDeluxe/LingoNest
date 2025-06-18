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
    $errors = [];

    if (empty($data["name"])) {
      $errors[] = "Name is required.";
    }
    if (empty($data["email"])) {
      $errors[] = "Email is required.";
    }
    if (empty($data["password"])) {
      $errors[] = "Password is required.";
    }

    if (isset($data["email"]) && !filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
       $errors[] = "Invalid email format.";
    }

    if (isset($data["password"])) {
      if (strlen($data["password"]) < 5) {
        $errors[] = "Password must be at least 5 characters long.";
      }

    if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $data['password'])) {
      $errors[] = "Password must contain at least one symbol.";
    }
    }
    return $errors;
  }
}