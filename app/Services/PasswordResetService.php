<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use PDO;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PasswordResetService
{
    private const TOKEN_EXPIRY_HOURS = 2;

    public function __construct(
        private PDO $pdo,
        private User $userModel,
        private array $config
    ) {
    }

    public function createToken(string $email): ?string
    {
        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + self::TOKEN_EXPIRY_HOURS * 3600);

        $stmt = $this->pdo->prepare('INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)');
        $stmt->execute([$user['id'], $token, $expiresAt]);

        return $token;
    }

    public function validateToken(string $token): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.*, u.id AS user_id, u.email FROM password_reset_tokens t 
             JOIN users u ON t.user_id = u.id 
             WHERE t.token = ? AND t.expires_at > NOW() AND u.is_active = 1'
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteToken(string $token): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM password_reset_tokens WHERE token = ?');
        $stmt->execute([$token]);
    }

    public function sendResetEmail(string $email, string $token): array
    {
        $appUrl = $this->config['app_url'] ?? 'http://localhost:8000';
        $link = $appUrl . '/reset-password?token=' . $token;

        $cfg = $this->config['mail'] ?? [];
        if (empty($cfg['host'])) {
            return ['sent' => false, 'dev_link' => $link];
        }

        $subject = 'Redefinição de senha - T Solutions';
        $body = "Você solicitou a redefinição de senha.\n\n";
        $body .= "Clique no link abaixo para definir uma nova senha (válido por " . self::TOKEN_EXPIRY_HOURS . " horas):\n\n";
        $body .= $link . "\n\n";
        $body .= "Se você não solicitou isso, ignore este e-mail.\n";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $cfg['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $cfg['username'];
            $mail->Password = $cfg['password'];
            $mail->SMTPSecure = $cfg['encryption'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $cfg['port'];
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($cfg['from'], $cfg['from_name']);
            $mail->addAddress($email);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->send();
            return ['sent' => true, 'dev_link' => null];
        } catch (Exception $e) {
            $logDir = dirname(__DIR__, 2) . '/storage/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            file_put_contents($logDir . '/app.log', date('Y-m-d H:i:s') . ' [mail_error] ' . $e->getMessage() . PHP_EOL, FILE_APPEND | LOCK_EX);
            return ['sent' => false, 'dev_link' => null];
        }
    }
}
