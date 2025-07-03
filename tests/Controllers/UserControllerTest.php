<?php

namespace Tests\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\UserController;
use App\Database;
use PDO;
use PDOStatement;

class UserControllerTest extends TestCase
{
  /*
   * Test the getProfile method for a successful case.
   */
  public function test_getProfile_successfully_returns_user_data(): void
  {
    // Define fake data. Expect DB to return, final JSON expected by controller to produce

    $fakeUser = ["id" => 1, "name" => "Johny Bravo", "email" => "johny@bravo.com"];
    $fakeLanguages = [["user_language_id" => 5, "language_id" => 1, "name" => "English", "status" => "native"]];
    $expectedJsonOutput = json_encode(array_merge($fakeUser, ["languages" => $fakeLanguages]));

    // Set up dummy env var
    $_ENV["JWT_SECRET"] = "test-secret";

    // Create mocks

    // Mock for the FIRST database call (getting the user)
    $mockUserStatement = $this->createMock(PDOStatement::class);
    $mockUserStatement->method("execute")->willReturn(true);
    $mockUserStatement->method("fetch")->willReturn($fakeUser);

    // Mock the SECOND database call (getting the language)
    $mockLangStatement = $this->createMock(PDOStatement::class);
    $mockLangStatement->method("execute")->willReturn(true);
    $mockLangStatement->method("fetchAll")->willReturn($fakeLanguages);

    // Mock for the main PDO connection object
    $mockPdo = $this->createMock(PDO::class);
    $mockPdo->method("prepare")
      ->willReturnOnConsecutiveCalls($mockUserStatement, $mockLangStatement);

    // Mock for database class wrapper
    $mockDatabase = $this->createMock(Database::class);
    $mockDatabase->method("getConnection")->willReturn($mockPdo);

    // Create a new UserController and give fake DB object
    $controller = new UserController($mockDatabase);

    ob_start();
    $controller->getProfile(1);
    $actualJsonOutput = ob_get_clean();

    // Assert
    $this->assertJsonStringEqualsJsonString($expectedJsonOutput, $actualJsonOutput);
  }

  /*
   * Test the getProfile method for a failure case where the user is NOT found
   */
    public function test_getProfile_returns_404_when_user_not_found(): void
    {
      $_ENV["JWT_SECRET"] = "test-secret";
      $expectedJsonOutput = json_encode(["message" => "User not found."]);

      // Configure mock statement to simulate what PDO does when no record found
      $mockUserStatement = $this->createMock(PDOStatement::class);
      $mockUserStatement->method("execute")->willReturn(true);
      $mockUserStatement->method("fetch")->willReturn(false); // Simulate not found


      $mockPdo = $this->createMock(PDO::class);
      $mockPdo->expects($this->once())
        ->method("prepare")
        ->willReturn($mockUserStatement);

      $mockDatabase = $this->createMock(Database::class);
      $mockDatabase->method("getConnection")->willReturn($mockPdo);

      // Act
      $controller = new UserController($mockDatabase);
      ob_start();
      $controller->getProfile(9999);
      $actualJsonOutput = ob_get_clean();

      // Assert
      $this->assertJsonStringEqualsJsonString($expectedJsonOutput, $actualJsonOutput);
      $this->assertEquals(404, http_response_code());
    }
}