<?php
/**
 * Route table. Every URL the application answers is listed here.
 */

use App\Controllers\AnalysisController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\PageController;
use App\Controllers\ReportController;
use Core\Router;

$router = new Router();

// Public
$router->get('/',              [HomeController::class, 'index']);
$router->get('/how-it-works',  [PageController::class, 'howItWorks']);

// Guest-only
$router->get('/login',     [AuthController::class, 'showLogin']);
$router->post('/login',    [AuthController::class, 'login']);
$router->get('/register',  [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);

// Authenticated
$router->get('/logout',      [AuthController::class, 'logout']);
$router->get('/dashboard',   [DashboardController::class, 'index']);
$router->get('/analysis',    [AnalysisController::class, 'create']);
$router->post('/analysis',   [AnalysisController::class, 'store']);
$router->get('/results',     [AnalysisController::class, 'history']);
$router->get('/report/{id}', [ReportController::class, 'show']);

return $router;
