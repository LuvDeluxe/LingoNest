<?php

namespace App;

use PDO;
use PDOException;

class Database
{
  public function __construct(private string $host, private string $name, private string $user, private string $password)
  {

  }

  /**
   * This is the main method of the class. It creates and returns a PDO connection object.
   */
  public function getConnection(): PDO
  {
    // DSN -> requires info required to connect to the db
    $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8";

    try {
      $conn = new PDO($dsn, $this->user, $this->password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false
      ]);
      return $conn;
    } catch (PDOException $e) {
      throw $e;
    }
  }

}