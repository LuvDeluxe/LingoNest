<?php

namespace App\Controllers;

use App\Database;
use PDO;
class LanguageController
{
  private PDO $conn;

  // Receive the database connection
  public function __construct(Database $database)
  {
    $this->conn = $database->getConnection();
  }

  /**
   * Fetches and returns the complete list of available languages.
   */
  public function getAllLanguages(): void
  {
    $sql = "SELECT id, name FROM languages ORDER BY name ASC";
    $stmt = $this->conn->query($sql);
    $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($languages);
  }
}