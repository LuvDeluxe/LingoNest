<?php
namespace App;

use ErrorException;
use Throwable;


class ErrorHandler
{
  /**
   * This method is registered in index.php to handle all PHP errors.
   * It converts them into ErrorException instances, which can then be caught.
   */
  public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
  {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
  }

  /**
   * This method is registered in index.php to handle any uncaught exceptions.
   * It stops the application, sets a "500 Internal Server Error" status code,
   * and sends a clean JSON response with the error details.
   */
    public static function handleException(Throwable $exception): void
    {
      http_response_code(500);

      echo json_encode([
        "code" => $exception->getCode(),
        "message" => $exception->getMessage(),
        "file" => $exception->getFile(),
        "line" => $exception->getLine()
      ]);
    }
}