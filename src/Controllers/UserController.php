<?php
namespace App\Controllers;

use App\Database;
use PDO;
use App\Services\Validator;

class UserController
{
  private PDO $conn;

  public function __construct(Database $database)
  {
    $this->conn = $database->getConnection();
  }

  /**
   * Handles the user registration process.
   * This method is called by the Router when a POST request is made to /api/register.
   */
  public function register(): void
  {
    // Get input data
    // The file_get_contents("php://input") function reads the raw request body.
    // json_decode then converts the JSON string into a PHP array.
    $data = (array) json_decode(file_get_contents("php://input"), true);

    $validator = new Validator();
    $errors = $validator->validateRegistration($data);

    if (!empty($errors)) {
      http_response_code(422);
      echo json_encode(["errors" => $errors]);
      return;
    }

    // Validate data
    if(!isset($data["name"]) || !isset($data["email"]) || !isset($data["password"])) {
      http_response_code(422); //unprocessable entry
      echo json_encode(["message" => "Missing required fields: name, email and password."]);
    }

    // Verify if user exists
    $sql = "SELECT id FROM users WHERE email = :email";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindValue(":email", $data["email"], PDO::PARAM_STR);
    if ($stmt->fetch() !== false) {
      http_response_code(409); // Conflict
      echo json_encode(["message" => "A user with this email address already exists."]);
      return;
    }

    // Hash the password
    $passwordHash = password_hash($data["password"], PASSWORD_DEFAULT);

    // Insert the new user into the Database
    $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
    $stmt = $this->conn->prepare($sql);

    $stmt->bindValue(":name", $data["name"], PDO::PARAM_STR);
    $stmt->bindValue(":email", $data["email"], PDO::PARAM_STR);
    $stmt->bindValue(":password", $passwordHash, PDO::PARAM_STR);

    $stmt->execute();

    http_response_code(201);
    echo json_encode(["message" => "User registration endpoint reached successfully",
      "user_id" => $this->conn->lastInsertId()]);
  }
}