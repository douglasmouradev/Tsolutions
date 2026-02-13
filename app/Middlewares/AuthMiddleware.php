<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Services\AuthService;

class AuthMiddleware
{
    public function __construct(
        private ?AuthService $authService = null
    ) {
    }

    public function handle(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
            exit;
        }

        if ($this->authService && $this->authService->mustChangePassword()) {
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
            if ($path !== '/change-password') {
                header('Location: /change-password');
                exit;
            }
        }

        return true;
    }

    public function requireRole(array $roles): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userRole = $_SESSION['user_role'] ?? null;
        if ($userRole === null || !in_array($userRole, $roles, true)) {
            http_response_code(403);
            echo 'Acesso negado.';
            exit;
        }

        return true;
    }
}
