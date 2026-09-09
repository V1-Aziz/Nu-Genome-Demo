<?php
/**
 * Front controller. Every request that is not a real file lands here
 * (see .htaccess) and is handed to the router.
 */

require __DIR__ . '/core/bootstrap.php';

/** @var Core\Router $router */
$router = require __DIR__ . '/config/routes.php';

$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/'
);
