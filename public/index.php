<?php

declare(strict_types=1);

header("content-type: application/json; charset=UTF-8");

require __DIR__ . "/Router.php";

$router = new Router();
$router->add("POST", "/api/register", function () {
  echo json_encode(["message" => "User registration endpoint !"]);
});

$router->dispatch();