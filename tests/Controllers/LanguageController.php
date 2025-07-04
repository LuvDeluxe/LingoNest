<?php

namespace PHPUnit\Framework\TestCase;
use App\Controllers\LanguageController;
use App\Database;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class LanguageController extends TestCase
{
  /*
   * Test the getAllLanguages method to ensure it returns a list of languages
   */

  public function test_getAllLanguages_returns_json_list_of_languages(): void
  {
    // Fake data we want the mock to return
    $fakeLanguages = [
      ["id" => 1, "name" => "English"],
      ["id" => 2, "name" => "Spanish"]
    ];

    // Expect controller to turn this array into a json string
    $expectedJsonOutput = json_encode($fakeLanguages);

    // Create a mock of the PDOStatement. When fetchAll method called, return fake language array
    $mockStatement = $this->createMock(PDOStatement::class);
    $mockStatement->method("fetchAll")->willReturn($fakeLanguages);

    // Create a mock of the main PDO connection object
    $mockPdo = $this->createMock(PDO::class);
    $mockPdo->method("query")->willReturn($mockStatement);

    // Create a mock of the DB
    $mockDatabase = $this->createMock(Database::class);
    $mockDatabase->method("getConnection")->willReturn($mockPdo);

    // Act
    $controller = new LanguageController($mockDatabase);

    // Use output buffering to catch the echo from the controller
    ob_start();
    $controller->getAllLanguages();
    $actualJsonOutput = ob_get_clean();

    // Assert
    $this->assertJsonStringEqualsJsonString($expectedJsonOutput, $actualJsonOutput);
  }
}