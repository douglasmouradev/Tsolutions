<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middlewares\Csrf;
use App\Models\User;
use App\Services\AuthService;
use App\Services\PasswordResetService;

class AuthController
{
    public function __construct(
        private AuthService $authService,
        private User $userModel,
        private PasswordResetService $passwordResetService
    ) {
    }

    public function showLogin(): void
    {
        if ($this->authService->isAuthenticated()) {
            if ($this->authService->mustChangePassword()) {
                header('Location: /change-password');
            } else {
                header('Location: /dashboard');
            }
            exit;
        }
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function login(): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido. Tente novamente.';
            header('Location: /login');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $_SESSION['flash_error'] = 'Preencha e-mail e senha.';
            header('Location: /login');
            exit;
        }

        $result = $this->authService->login($email, $password);

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];
            header('Location: /login');
            exit;
        }

        if ($result['user']['must_change_password'] ?? false) {
            header('Location: /change-password');
            exit;
        }

        $redirect = $_GET['redirect'] ?? '/dashboard';
        header('Location: ' . (str_starts_with($redirect, '/') ? $redirect : '/dashboard'));
        exit;
    }

    public function showChangePassword(): void
    {
        if (!$this->authService->isAuthenticated()) {
            header('Location: /login');
            exit;
        }
        if (!$this->authService->mustChangePassword()) {
            header('Location: /dashboard');
            exit;
        }
        require __DIR__ . '/../Views/auth/change-password.php';
    }

    public function changePassword(): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /change-password');
            exit;
        }
        if (!$this->authService->isAuthenticated()) {
            header('Location: /login');
            exit;
        }
        if (!$this->authService->mustChangePassword()) {
            header('Location: /dashboard');
            exit;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $user = $this->authService->currentUser();
        $dbUser = $this->userModel->find((int) $user['id']);
        if (!$dbUser || !password_verify($currentPassword, $dbUser['password_hash'])) {
            $_SESSION['flash_error'] = 'Senha atual incorreta.';
            header('Location: /change-password');
            exit;
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['flash_error'] = 'A nova senha deve ter pelo menos 6 caracteres.';
            header('Location: /change-password');
            exit;
        }
        if ($newPassword !== $confirmPassword) {
            $_SESSION['flash_error'] = 'A confirmação da nova senha não confere.';
            header('Location: /change-password');
            exit;
        }

        $this->userModel->update((int) $user['id'], [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'must_change_password' => 0,
        ]);

        $_SESSION['user']['must_change_password'] = false;
        $_SESSION['flash_success'] = 'Senha alterada com sucesso.';
        header('Location: /dashboard');
        exit;
    }

    public function showForgotPassword(): void
    {
        if ($this->authService->isAuthenticated()) {
            header('Location: /dashboard');
            exit;
        }
        require __DIR__ . '/../Views/auth/forgot-password.php';
    }

    public function forgotPassword(): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /forgot-password');
            exit;
        }
        $email = trim($_POST['email'] ?? '');
        if ($email === '') {
            $_SESSION['flash_error'] = 'Informe seu e-mail.';
            header('Location: /forgot-password');
            exit;
        }

        $token = $this->passwordResetService->createToken($email);
        if ($token) {
            $result = $this->passwordResetService->sendResetEmail($email, $token);
            if ($result['sent']) {
                $_SESSION['flash_success'] = 'Se o e-mail estiver cadastrado, você receberá um link para redefinir sua senha.';
            } elseif ($result['dev_link'] ?? null) {
                $_SESSION['flash_success'] = 'E-mail não configurado. Use este link (válido por 2h): ' . $result['dev_link'];
            } else {
                $_SESSION['flash_error'] = 'Erro ao enviar e-mail. Tente novamente ou contate o administrador.';
            }
        } else {
            $_SESSION['flash_success'] = 'Se o e-mail estiver cadastrado, você receberá um link para redefinir sua senha.';
        }
        header('Location: /forgot-password');
        exit;
    }

    public function showResetPassword(): void
    {
        $token = trim($_GET['token'] ?? '');
        if ($token === '') {
            $_SESSION['flash_error'] = 'Link inválido ou expirado.';
            header('Location: /forgot-password');
            exit;
        }
        $data = $this->passwordResetService->validateToken($token);
        if (!$data) {
            $_SESSION['flash_error'] = 'Link inválido ou expirado. Solicite uma nova redefinição.';
            header('Location: /forgot-password');
            exit;
        }
        require __DIR__ . '/../Views/auth/reset-password.php';
    }

    public function resetPassword(): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /forgot-password');
            exit;
        }
        $token = trim($_POST['token'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $data = $this->passwordResetService->validateToken($token);
        if (!$data) {
            $_SESSION['flash_error'] = 'Link inválido ou expirado. Solicite uma nova redefinição.';
            header('Location: /forgot-password');
            exit;
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['flash_error'] = 'A senha deve ter pelo menos 6 caracteres.';
            header('Location: /reset-password?token=' . urlencode($token));
            exit;
        }
        if ($newPassword !== $confirmPassword) {
            $_SESSION['flash_error'] = 'A confirmação da senha não confere.';
            header('Location: /reset-password?token=' . urlencode($token));
            exit;
        }

        $this->userModel->update((int) $data['user_id'], [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'must_change_password' => 0,
        ]);
        $this->passwordResetService->deleteToken($token);

        $_SESSION['flash_success'] = 'Senha alterada com sucesso. Faça login.';
        header('Location: /login');
        exit;
    }

    public function logout(): void
    {
        if (!Csrf::validate()) {
            header('Location: /dashboard');
            exit;
        }
        $this->authService->logout();
        header('Location: /login');
        exit;
    }
}
