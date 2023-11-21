<?php

require_once './vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();

use Src\ApiController;

$controller = new ApiController();

echo $controller->getAPIData($_REQUEST);
