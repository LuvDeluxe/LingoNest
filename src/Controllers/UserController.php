<?php
namespace App\Controllers;

use App\Database;
use PDO;
use App\Services\Validator;
use App\Services\JwtService;

class UserController
{
  private PDO $conn;
  private JwtService $jwtService;

  public function __construct(Database $database)
  {
    $this->conn = $database->getConnection();
    $this->jwtService = new JwtService();
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

  /**
   * Handles the user login process.
   */
  public function login(): void
  {
    // Get input data
    $data = (array) json_decode(file_get_contents("php://input"), true);

    // Validate
    if (!isset($data["email"]) || !isset($data["password"])) {
      http_response_code(422);
      echo json_encode(["message" => "Missing email or password"]);
      return;
    }

    // Find user by email
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindValue(":email", $data["email"], PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify User and password
    if ($user === false || !password_verify($data["password"], $user["password"])) {
      http_response_code(401);
      echo json_encode(["message" => "Invalid credentials."]);
      return;
    }

    // Generate a JWT
    $token = $this->jwtService->generate((int) $user["id"]);

    echo json_encode(["access token" => $token]);
  }

  /*
   * Gets the profile information for the authenticated user
   */
  public function getProfile(): void
  {
    // Get the Authorization header from the request
    $authHeader = $_SERVER["HTTP_AUTHORIZATION"] ?? null;

    if ($authHeader === null) {
      http_response_code(401);
      echo json_encode(["message" => "Authorization token not found."]);
      return;
    }

    if (!preg_match("/^Bearer\s+(.*)$/", $authHeader, $matches)) {
      http_response_code(401);
      echo json_encode(["Message" => "Invalid token format."]);
      return;
    }

    $token = $matches[1];

    // Validate the token
    $userId = $this->jwtService->validate($token);

    if ($userId === null) {
      http_response_code(401);
      echo json_encode(["message" => "Invalid or expired token."]);
    }

    // If the token is valid, fetch the users data
    $sql = "SELECT id, name, email, created_at FROM users WHERE id = :id";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindValue(":id", $userId, PDO::PARAM_INT);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user === false) {
      // Not found
      http_response_code(404);
      echo json_encode(["message" => "User not found."]);
      return;
    }

    // Send the users public data back
    echo json_encode($user);
  }

  /*
   * Adds a language to the authenticated users profile
   */
  public function addUserLanguage(): void
  {
    // Auth the user
    $authHeader = $_SERVER["HTTP_AUTHORIZATION"] ?? null;
    if ($authHeader === null || !preg_match("/^Bearer\s+(.*)$/", $authHeader, $matches)) {
      http_response_code(401);
      echo json_encode(["message" => "Authorization token not found or not valid."]);
      return;
    }

    $userId = $this->jwtService->validate($matches[1]);

    if ($userId === null) {
      http_response_code(401);
      echo json_encode(["message" => "Invalid or expired token."]);
      return;
    }

    // Get the input data
    $data = (array) json_decode(file_get_contents("php://input"), true);

    // Validate the input
    if (empty($data["language_id"]) || empty($data["status"])) {
      http_response_code(422);
      echo json_encode(['errors' => ['language_id and status are required.']]);
      return;
    }

    if (!in_array($data["status"], ["native", "learning"])) {
      http_response_code(422);
      echo json_encode(["errors" => "status must be either native or learning"]);
      return;
    }

    // Insert the data into user_languages
    $sql = "INSERT INTO user_languages (user_id, language_id, status) VALUES (:user_id, :language_id, :status)";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
    $stmt->bindValue(":language_id", $data["language_id"], PDO::PARAM_INT);
    $stmt->bindValue(":status", $data["status"], PDO::PARAM_INT);

    try {
      $stmt->execute();
      http_response_code(201);
      echo json_decode(["message" => "Language added to profile successfully"]);
    } catch (\PDOException $e) {
      if ($e->getCode() === "23000") {
        http_response_code(409);
        echo json_encode(["message" => "This language has already been added to your profile"]);
      }
      else {
        throw $e;
      }
    }
  }

  //dsa
}