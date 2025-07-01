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

  }
}