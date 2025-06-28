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
    if (empty($data["email"]) || empty($data["password"])) {
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
  public function getProfile(int $userId): void
  {
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

    $sql = "SELECT l.id AS language_id, l.name, ul.status, ul.id AS user_language_id
            FROM user_languages ul
            JOIN languages l ON ul.language_id = l.id
            WHERE ul.user_id = :user_id";

    $langStmt = $this->conn->prepare($sql);
    $langStmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
    $langStmt->execute();
    $languages = $langStmt->fetchAll(PDO::FETCH_ASSOC);

    $user["languages"] = $languages;

    // Send the users public data back
    echo json_encode($user);
  }

  /*
   * Adds a language to the authenticated users profile
   */
  public function addUserLanguage(int $userId): void
  {
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
    $stmt->bindValue(":status", $data["status"], PDO::PARAM_STR);

    try {
      $stmt->execute();
      http_response_code(201);
      echo json_encode(["message" => "Language added to profile successfully"]);
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

  /**
   * Deletes a language from the authenticated users profile
   * @param int $userId
   * @return void
   */
  public function deleteUserLanguage(int $userId): void
  {
    $data = (array) json_decode(file_get_contents("php://input"), true);

    if (empty($data["user_language_id"])) {
      http_response_code(422);
      echo json_encode(["errors" => "user_language_id is required"]);
      return;
    }

    $sql = "DELETE FROM user_languages 
             WHERE id = :user_language_id AND user_id = :user_id";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindValue(":user_language_id", $data["user_language_id"], PDO::PARAM_INT);
    $stmt->bindValue("user_id", $userId, PDO::PARAM_INT);

    $stmt->execute();

    // if rowCount returns 0 it means there is an error. If successful will return 1
    if ($stmt->rowCount() === 0) {
      http_response_code(404);
      json_encode(["message" => "Language not found on profile or you do not have permission to delete it"]);
      return;
    }

    // send a success report. 204 no content is standard for successful DELETEs.
    http_response_code(204);
  }

  /*
   * Finds language exchange partners for the authenticated user
   */
  public function getMatches(int $userId): void
  {
    $sql = "
            SELECT
                u.id, u.name, u.email, u.created_at
            FROM
                user_languages AS my_native_languages
            JOIN
                user_languages AS partner_learning_languages
                ON my_native_languages.language_id = partner_learning_languages.language_id
                AND my_native_languages.user_id != partner_learning_languages.user_id
            JOIN
                user_languages AS my_learning_languages
                ON partner_learning_languages.user_id = my_learning_languages.user_id
            JOIN
                user_languages AS partner_native_languages
                ON my_learning_languages.language_id = partner_native_languages.language_id
            JOIN
                users u ON partner_learning_languages.user_id = u.id
            WHERE
                my_native_languages.user_id = :current_user_id
                AND my_native_languages.status = 'native'
                AND partner_learning_languages.status = 'learning'
                AND my_learning_languages.status = 'learning'
                AND partner_native_languages.status = 'native'
            GROUP BY
                u.id, u.name, u.email, u.created_at";

    $stmt = $this->conn->prepare($sql);

    $stmt->bindValue(":current_user_id", $userId, PDO::PARAM_INT);
    $stmt->execute();

    $matches = $stmt->fetchAll();

    echo json_encode($matches);
  }
}