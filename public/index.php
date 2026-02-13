<?php

declare(strict_types=1);

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: no-referrer-when-downgrade');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data:; connect-src 'self' https://viacep.com.br");

require dirname(__DIR__) . '/vendor/autoload.php';

$config = require dirname(__DIR__) . '/config/config.php';
date_default_timezone_set($config['timezone'] ?? 'UTC');

$isProduction = ($config['app_env'] ?? '') === 'production';

if ($isProduction) {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} elseif (!($config['app_debug'] ?? false)) {
    ini_set('display_errors', '0');
}

if ($isProduction && empty($_SERVER['HTTPS']) && ($_SERVER['SERVER_PORT'] ?? 80) == 80) {
    $url = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
    header('Location: ' . $url, true, 301);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isProduction || !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => $isProduction ? 'Strict' : 'Lax',
    ]);
    session_start();
}

$pdo = require dirname(__DIR__) . '/config/database.php';

$userModel = new \App\Models\User($pdo);
$ticketModel = new \App\Models\Ticket($pdo);
$commentModel = new \App\Models\TicketComment($pdo);
$statusHistoryModel = new \App\Models\TicketStatusHistory($pdo);
$attachmentModel = new \App\Models\Attachment($pdo);
$categoryModel = new \App\Models\Category($pdo);
$tecnicoModel = new \App\Models\Tecnico($pdo);
$unidadeModel = new \App\Models\Unidade($pdo);

$authService = new \App\Services\AuthService($pdo, $userModel);
$uploadPath = dirname(__DIR__) . '/storage/uploads';
$quarantinePath = dirname(__DIR__) . '/storage/quarantine';
$uploadService = new \App\Services\UploadService(
    $uploadPath,
    $quarantinePath,
    $config['max_upload_mb'] * 1024 * 1024,
    $config['clamav_path'] ?? null
);

$authMiddleware = new \App\Middlewares\AuthMiddleware($authService);

$configFull = require dirname(__DIR__) . '/config/config.php';
$passwordResetService = new \App\Services\PasswordResetService($pdo, $userModel, $configFull);
$notificationService = new \App\Services\NotificationService($configFull, $userModel);
$authController = new \App\Controllers\AuthController($authService, $userModel, $passwordResetService);
$dashboardController = new \App\Controllers\DashboardController($ticketModel, $authService);
$ticketController = new \App\Controllers\TicketController(
    $ticketModel,
    $commentModel,
    $statusHistoryModel,
    $attachmentModel,
    $categoryModel,
    $userModel,
    $unidadeModel,
    $authService,
    $uploadService,
    $notificationService,
    $configFull
);
$userController = new \App\Controllers\UserController($userModel, $authService);
$categoryController = new \App\Controllers\CategoryController($categoryModel, $authService);
$tecnicoController = new \App\Controllers\TecnicoController($tecnicoModel, $authService);
$unidadeController = new \App\Controllers\UnidadeController($unidadeModel, $authService);
$reportController = new \App\Controllers\ReportController($ticketModel, $categoryModel, $userModel, $authService);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET' => [
        '/' => fn() => header('Location: /dashboard') && exit,
        '/login' => fn() => $authController->showLogin(),
        '/forgot-password' => fn() => $authController->showForgotPassword(),
        '/reset-password' => fn() => $authController->showResetPassword(),
        '/change-password' => function () use ($authMiddleware, $authController) {
            $authMiddleware->handle();
            $authController->showChangePassword();
        },
        '/dashboard' => function () use ($authMiddleware, $dashboardController) {
            $authMiddleware->handle();
            $dashboardController->index();
        },
        '/tickets' => function () use ($authMiddleware, $ticketController) {
            $authMiddleware->handle();
            $ticketController->index();
        },
        '/tickets/create' => function () use ($authMiddleware, $ticketController) {
            $authMiddleware->handle();
            $ticketController->create();
        },
        '/tickets/{id}' => function (int $id) use ($authMiddleware, $ticketController) {
            $authMiddleware->handle();
            $ticketController->show($id);
        },
        '/tickets/{id}/edit' => function (int $id) use ($authMiddleware, $ticketController) {
            $authMiddleware->handle();
            $ticketController->edit($id);
        },
        '/users' => function () use ($authMiddleware, $userController) {
            $authMiddleware->handle();
            $authMiddleware->requireRole(['admin']);
            $userController->index();
        },
        '/categories' => function () use ($authMiddleware, $categoryController) {
            $authMiddleware->handle();
            $authMiddleware->requireRole(['admin']);
            $categoryController->index();
        },
        '/tecnicos' => function () use ($authMiddleware, $tecnicoController) {
            $authMiddleware->handle();
            $authMiddleware->requireRole(['admin']);
            $tecnicoController->index();
        },
        '/tecnicos/{id}/edit' => function (int $id) use ($authMiddleware, $tecnicoController) {
            $authMiddleware->handle();
            $authMiddleware->requireRole(['admin']);
            $tecnicoController->edit($id);
        },
        '/unidades' => function () use ($authMiddleware, $unidadeController) {
            $authMiddleware->handle();
            $authMiddleware->requireRole(['admin']);
            $unidadeController->index();
        },
        '/reports' => function () use ($authMiddleware, $reportController) {
            $authMiddleware->handle();
            $reportController->index();
        },
        '/reports/export-excel' => function () use ($authMiddleware, $reportController) {
            $authMiddleware->handle();
            $reportController->exportExcel();
        },
        '/reports/export-pdf' => function () use ($authMiddleware, $reportController) {
            $authMiddleware->handle();
            $reportController->exportPdf();
        },
        '/tickets/{id}/attachments/{aid}/download' => function (int $id, int $aid) use ($authMiddleware, $ticketController) {
            $authMiddleware->handle();
            $ticketController->downloadAttachment($id, $aid);
        },
    ],
    'POST' => [
        '/login' => fn() => $authController->login(),
        '/forgot-password' => fn() => $authController->forgotPassword(),
        '/reset-password' => fn() => $authController->resetPassword(),
        '/change-password' => fn() => $authController->changePassword(),
        '/logout' => fn() => $authController->logout(),
        '/tickets' => function () use ($authMiddleware, $ticketController) {
            $authMiddleware->handle();
            $ticketController->store();
        },
        '/tickets/{id}/update' => function (int $id) use ($authMiddleware, $ticketController) {
            $authMiddleware->handle();
            $ticketController->update($id);
        },
        '/tickets/{id}/status' => function (int $id) use ($authMiddleware, $ticketController) {
            $authMiddleware->handle();
            $ticketController->changeStatus($id);
        },
        '/tickets/{id}/assign' => function (int $id) use ($authMiddleware, $ticketController) {
            $authMiddleware->handle();
            $ticketController->assign($id);
        },
        '/tickets/{id}/comments' => function (int $id) use ($authMiddleware, $ticketController) {
            $authMiddleware->handle();
            $ticketController->addComment($id);
        },
        '/tickets/{id}/attachments' => function (int $id) use ($authMiddleware, $ticketController) {
            $authMiddleware->handle();
            $ticketController->uploadAttachment($id);
        },
        '/users' => function () use ($authMiddleware, $userController) {
            $authMiddleware->handle();
            $authMiddleware->requireRole(['admin']);
            $userController->store();
        },
        '/categories' => function () use ($authMiddleware, $categoryController) {
            $authMiddleware->handle();
            $authMiddleware->requireRole(['admin']);
            $categoryController->store();
        },
        '/categories/{id}/update' => function (int $id) use ($authMiddleware, $categoryController) {
            $authMiddleware->handle();
            $authMiddleware->requireRole(['admin']);
            $categoryController->update($id);
        },
        '/categories/{id}/delete' => function (int $id) use ($authMiddleware, $categoryController) {
            $authMiddleware->handle();
            $authMiddleware->requireRole(['admin']);
            $categoryController->delete($id);
        },
        '/tecnicos' => function () use ($authMiddleware, $tecnicoController) {
            $authMiddleware->handle();
            $authMiddleware->requireRole(['admin']);
            $tecnicoController->store();
        },
        '/tecnicos/{id}/update' => function (int $id) use ($authMiddleware, $tecnicoController) {
            $authMiddleware->handle();
            $authMiddleware->requireRole(['admin']);
            $tecnicoController->update($id);
        },
        '/tecnicos/{id}/delete' => function (int $id) use ($authMiddleware, $tecnicoController) {
            $authMiddleware->handle();
            $authMiddleware->requireRole(['admin']);
            $tecnicoController->delete($id);
        },
        '/unidades' => function () use ($authMiddleware, $unidadeController) {
            $authMiddleware->handle();
            $authMiddleware->requireRole(['admin']);
            $unidadeController->store();
        },
        '/unidades/{id}/update' => function (int $id) use ($authMiddleware, $unidadeController) {
            $authMiddleware->handle();
            $authMiddleware->requireRole(['admin']);
            $unidadeController->update($id);
        },
        '/unidades/{id}/delete' => function (int $id) use ($authMiddleware, $unidadeController) {
            $authMiddleware->handle();
            $authMiddleware->requireRole(['admin']);
            $unidadeController->delete($id);
        },
    ],
];

$methodRoutes = $routes[$method] ?? [];

foreach ($methodRoutes as $pattern => $handler) {
    $regex = preg_replace('/\{([^}]+)\}/', '(?P<$1>\d+)', $pattern);
    $regex = '#^' . $regex . '$#';
    if (preg_match($regex, $uri, $matches)) {
        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        $ids = array_map('intval', array_values($params));
        $handler(...$ids);
        exit;
    }
}

http_response_code(404);
echo '404 - Página não encontrada';
