<?php
namespace Tests\Services;

// Use test case
use PHPUnit\Framework\TestCase;

// Import the class to be tested
use App\Services\Validator;

class ValidatorTest extends TestCase
{
  /*
   * Verify happy path
   */
  public function test_validateRegistration_with_valid_data(): void
{
  // Set up the test. Create an instance of validator and prepare perfect input data
  $validator = new Validator();
  $validData = [
    "name" => "Bill Jobs",
    "email" => "bill.jobs@pear.com",
    "password" => "Password!1234"
  ];

  // Perform action
  $errors = $validator->validateRegistration($validData);

  // Assert
  $this->assertEmpty($errors);
}

/*
 * Verify unhappy path
 */
  public function test_validateRegistration_with_invalid_data(): void
  {
    // Set up the test. Create an instance of validator and prepare bad data
    $validator = new Validator();
    $invalidData = [
      "name" => "iO",
      "email" => "totallyvalidemail",
      "password" => "321",
    ];

    // Action
    $errors = $validator->validateRegistration($invalidData);

    // Assert
    $this->assertNotEmpty($errors);

    // Check error messages
    $this->assertContains("Invalid email format.", $errors);
    $this->assertContains("Password must be at least 5 characters long.", $errors);
    $this->assertContains("Password must contain at least one symbol.", $errors);
  }

  /*
   * Verify valid email, invalid password
   */
  public function test_validateRegistration_valid_email_invalid_password(): void
  {
    $validator = new Validator();

    $data = [
      "name" => "Sancho",
      "email" => "meon1266@gmail.com",
      "password" => "haha1234Moni"
    ];

    $errors = $validator->validateRegistration($data);

    $this->assertNotEmpty($errors);

    $this->assertContains("Password must contain at least one symbol.", $errors);
  }

  /*
   * Verify empty email
   */
  public function test_validate_registration_missing_email(): void
  {
    $validator = new Validator();

    $data = [
      "name" => "Sancho",
      "password" => "Th1s!isaP@ssword"
    ];

    $errors = $validator->validateRegistration($data);

    $this->assertNotEmpty($errors);
    $this->assertContains("Email is required.", $errors);
  }

  /*
   * Verify empty username
   */

  public function test_validate_registration_missing_username(): void
  {
    $validator = new Validator();

    $data = [
      "email" => "simeon@gmail.com",
      "password" => "Th1s!isaP@ssword"
    ];

    $errors = $validator->validateRegistration($data);

    $this->assertNotEmpty($errors);
    $this->assertContains("Name is required.", $errors);
  }

  /*
   * Verify empty password
   */

  public function test_validate_registration_missing_password(): void
  {
    $validator = new Validator();

    $data = [
      "email" => "simeon@gmail.com",
      "name" => "Sancho"
    ];

    $errors = $validator->validateRegistration($data);

    $this->assertNotEmpty($errors);
    $this->assertContains("Password is required.", $errors);
  }
}

