<?php
declare(strict_types=1);

session_start();

define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', __DIR__);

require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/app/helpers.php';
require_once APP_ROOT . '/app/models/UserModel.php';
require_once APP_ROOT . '/app/models/PostModel.php';
require_once APP_ROOT . '/app/models/CommentModel.php';
require_once APP_ROOT . '/app/models/CommentReactionModel.php';
require_once APP_ROOT . '/app/models/LikeModel.php';
require_once APP_ROOT . '/app/models/MessageModel.php';

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    http_response_code(500);
    $lastError = $db->getLastError() ?? 'unknown';
    error_log('Database connection failed in bootstrap: ' . $lastError);
    echo 'Database connection failed. Verify DB_HOST, DB_PORT, DB_NAME, DB_USER, and DB_PASSWORD.';
    exit;
}

$controller = $_GET['controller'] ?? (app_is_authenticated() ? 'Feed' : 'Auth');
$action = $_GET['action'] ?? ($controller === 'Auth' ? 'login' : 'index');
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

$routes = [
    'Auth' => [
        'file' => APP_ROOT . '/app/controllers/AuthController.php',
        'class' => 'AuthController',
        'requiresAuth' => false,
        'actions' => ['login', 'register', 'logout'],
    ],
    'Feed' => [
        'file' => APP_ROOT . '/app/controllers/FeedController.php',
        'class' => 'FeedController',
        'requiresAuth' => true,
        'actions' => ['index'],
    ],
    'Profile' => [
        'file' => APP_ROOT . '/app/controllers/ProfileController.php',
        'class' => 'ProfileController',
        'requiresAuth' => true,
        'actions' => ['view', 'edit'],
    ],
    'Post' => [
        'file' => APP_ROOT . '/app/controllers/PostController.php',
        'class' => 'PostController',
        'requiresAuth' => true,
        'actions' => ['create', 'share', 'edit', 'delete'],
    ],
    'Comment' => [
        'file' => APP_ROOT . '/app/controllers/CommentController.php',
        'class' => 'CommentController',
        'requiresAuth' => true,
        'actions' => ['create', 'edit', 'delete', 'react'],
    ],
    'Like' => [
        'file' => APP_ROOT . '/app/controllers/LikeController.php',
        'class' => 'LikeController',
        'requiresAuth' => true,
        'actions' => ['toggle'],
    ],
    'Message' => [
        'file' => APP_ROOT . '/app/controllers/MessageController.php',
        'class' => 'MessageController',
        'requiresAuth' => true,
        'actions' => ['index', 'send', 'react', 'deleteConversation', 'unsend', 'remove'],
    ],
];

if (!isset($routes[$controller]) || !in_array($action, $routes[$controller]['actions'], true)) {
    http_response_code(404);
    echo 'Page not found.';
    exit;
}

$route = $routes[$controller];

if ($route['requiresAuth']) {
    app_require_auth();
}

require_once $route['file'];

$controllerClass = $route['class'];
$controllerInstance = new $controllerClass($conn);

if (!method_exists($controllerInstance, $action)) {
    http_response_code(404);
    echo 'Page not found.';
    exit;
}

$controllerInstance->{$action}($id);

