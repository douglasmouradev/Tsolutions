<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use PDO;

class AuthService
{
    private const RATE_LIMIT_ATTEMPTS = 5;
    private const RATE_LIMIT_WINDOW = 900; // 15 min em segundos
    private const SESSION_KEY_USER = 'user';
    private const SESSION_KEY_USER_ID = 'user_id';
    private const SESSION_KEY_USER_ROLE = 'user_role';

    public function __construct(
        private PDO $pdo,
        private User $userModel
    ) {
    }

    public function login(string $email, string $password): array
    {
        if ($this->isRateLimited($email)) {
            $this->log('login_fail_rate_limit', ['email' => $email]);
            return ['success' => false, 'message' => 'Muitas tentativas. Aguarde 15 minutos.'];
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->recordFailedAttempt($email);
            $this->log('login_fail', ['email' => $email]);
            return ['success' => false, 'message' => 'Credenciais inválidas.'];
        }

        $this->clearFailedAttempts($email);
        $this->startSession($user);
        $this->log('login_success', ['user_id' => $user['id'], 'email' => $email]);
        return ['success' => true, 'user' => $user];
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public function currentUser(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION[self::SESSION_KEY_USER] ?? null;
    }

    public function isAuthenticated(): bool
    {
        return $this->currentUser() !== null;
    }

    public function hasRole(string|array $roles): bool
    {
        $user = $this->currentUser();
        if (!$user) {
            return false;
        }
        $userRole = $user['role'] ?? null;
        if (is_array($roles)) {
            return in_array($userRole, $roles, true);
        }
        return $userRole === $roles;
    }

    /** Permite visualizar chamado (página de detalhes). Todos os usuários autenticados podem ver todos os chamados. */
    public function canViewTicket(array $ticket): bool
    {
        return $this->currentUser() !== null;
    }

    public function canManageTicket(array $ticket): bool
    {
        $user = $this->currentUser();
        if (!$user) {
            return false;
        }
        if (in_array($user['role'], ['admin', 'diretoria'], true)) {
            return true;
        }
        if (in_array($user['role'], ['agent', 'suporte'], true)) {
            return (int) $ticket['agent_id'] === (int) $user['id'] || $ticket['agent_id'] === null;
        }
        if (in_array($user['role'], ['requester', 'externo'], true)) {
            if ((int) $ticket['requester_id'] === (int) $user['id']) {
                return true;
            }
            $status = $ticket['status'] ?? '';
            if (in_array($status, ['aberto', 'em_andamento'], true)) {
                return true;
            }
        }
        return false;
    }

    public function canCloseTicket(array $ticket): bool
    {
        $user = $this->currentUser();
        if (!$user) {
            return false;
        }
        $config = require dirname(__DIR__, 2) . '/config/config.php';
        $allowRequesterClose = $config['allow_requester_close'] ?? false;

        if (in_array($user['role'], ['admin', 'diretoria', 'agent', 'suporte'], true)) {
            return $this->canManageTicket($ticket);
        }
        if (in_array($user['role'], ['requester', 'externo'], true)) {
            return $allowRequesterClose && (int) $ticket['requester_id'] === (int) $user['id'];
        }
        return false;
    }

    private function startSession(array $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY_USER_ID] = (int) $user['id'];
        $_SESSION[self::SESSION_KEY_USER_ROLE] = $user['role'];
        $_SESSION[self::SESSION_KEY_USER] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'must_change_password' => (bool) ($user['must_change_password'] ?? false),
        ];
    }

    public function mustChangePassword(): bool
    {
        $user = $this->currentUser();
        return $user && ($user['must_change_password'] ?? false);
    }

    private function getRateLimitKey(string $email): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return 'auth_fail_' . md5($ip . '|' . strtolower($email));
    }

    private function isRateLimited(string $email): bool
    {
        $key = $this->getRateLimitKey($email);
        $stmt = $this->pdo->prepare('SELECT attempts, first_attempt FROM auth_rate_limit WHERE key_hash = ?');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        if (!$row) {
            return false;
        }
        $elapsed = time() - (int) strtotime($row['first_attempt']);
        if ($elapsed > self::RATE_LIMIT_WINDOW) {
            $this->clearFailedAttempts($email);
            return false;
        }
        return (int) $row['attempts'] >= self::RATE_LIMIT_ATTEMPTS;
    }

    private function recordFailedAttempt(string $email): void
    {
        $key = $this->getRateLimitKey($email);
        $stmt = $this->pdo->prepare(
            'INSERT INTO auth_rate_limit (key_hash, attempts, first_attempt) VALUES (?, 1, NOW())
             ON DUPLICATE KEY UPDATE attempts = attempts + 1'
        );
        $stmt->execute([$key]);
    }

    private function clearFailedAttempts(string $email): void
    {
        $key = $this->getRateLimitKey($email);
        $stmt = $this->pdo->prepare('DELETE FROM auth_rate_limit WHERE key_hash = ?');
        $stmt->execute([$key]);
    }

    private function log(string $event, array $data): void
    {
        $logDir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $line = date('Y-m-d H:i:s') . ' [' . $event . '] ' . json_encode($data) . PHP_EOL;
        file_put_contents($logDir . '/app.log', $line, FILE_APPEND | LOCK_EX);
    }
}
