<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middlewares\Csrf;
use App\Models\User;
use App\Services\AuthService;

class UserController
{
    public function __construct(
        private User $userModel,
        private AuthService $authService
    ) {
    }

    public function index(): void
    {
        $users = $this->userModel->findAllActive();
        $currentUser = $this->authService->currentUser();
        require __DIR__ . '/../Views/users/index.php';
    }

    public function store(): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /users');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'requester';

        $errors = [];
        if (strlen($name) < 2) {
            $errors[] = 'Nome deve ter pelo menos 2 caracteres.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'E-mail inválido.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Senha deve ter pelo menos 6 caracteres.';
        }
        if (!in_array($role, ['admin', 'agent', 'requester', 'diretoria', 'externo', 'suporte'], true)) {
            $errors[] = 'Papel inválido.';
        }

        if ($this->userModel->findByEmail($email)) {
            $errors[] = 'E-mail já cadastrado.';
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            header('Location: /users');
            exit;
        }

        $this->userModel->create([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
        ]);

        $_SESSION['flash_success'] = 'Usuário criado.';
        header('Location: /users');
        exit;
    }
}
