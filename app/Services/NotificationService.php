<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class NotificationService
{
    public function __construct(
        private array $config,
        private User $userModel
    ) {
    }

    private function send(string $to, string $subject, string $body): bool
    {
        $cfg = $this->config['mail'] ?? [];
        if (empty($cfg['host'])) {
            return false;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $cfg['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $cfg['username'];
            $mail->Password = $cfg['password'];
            $mail->SMTPSecure = $cfg['encryption'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $cfg['port'];
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($cfg['from'], $cfg['from_name']);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->send();
            return true;
        } catch (Exception $e) {
            $logDir = dirname(__DIR__, 2) . '/storage/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            file_put_contents($logDir . '/app.log', date('Y-m-d H:i:s') . ' [notification_error] ' . $e->getMessage() . PHP_EOL, FILE_APPEND | LOCK_EX);
            return false;
        }
    }

    public function notifyTicketCreated(array $ticket, array $requester): void
    {
        $appUrl = rtrim($this->config['app_url'] ?? '', '/');
        $link = $appUrl . '/tickets/' . $ticket['id'];

        $subject = '[T Solutions] Novo chamado #' . $ticket['id'] . ': ' . ($ticket['title'] ?? '');
        $body = "Um novo chamado foi aberto.\n\n";
        $body .= "Título: " . ($ticket['title'] ?? '') . "\n";
        $body .= "Solicitante: " . ($requester['name'] ?? '') . " (" . ($requester['email'] ?? '') . ")\n";
        $body .= "Prioridade: " . (priorityLabel($ticket['priority'] ?? 'baixa') ?? 'Baixa') . "\n\n";
        $body .= "Acesse: " . $link . "\n";

        foreach ($this->getNotificationRecipients() as $email) {
            if ($email !== ($requester['email'] ?? '')) {
                $this->send($email, $subject, $body);
            }
        }
    }

    public function notifyTicketAssigned(array $ticket, array $agent): void
    {
        if (empty($agent['email'])) {
            return;
        }

        $appUrl = rtrim($this->config['app_url'] ?? '', '/');
        $link = $appUrl . '/tickets/' . $ticket['id'];

        $subject = '[T Solutions] Chamado #' . $ticket['id'] . ' atribuído a você';
        $body = "O chamado #" . $ticket['id'] . " foi atribuído a você.\n\n";
        $body .= "Título: " . ($ticket['title'] ?? '') . "\n";
        $body .= "Solicitante: " . ($ticket['requester_name'] ?? '-') . "\n\n";
        $body .= "Acesse: " . $link . "\n";

        $this->send($agent['email'], $subject, $body);
    }

    public function notifyCommentAdded(array $ticket, array $commentAuthor, string $commentBody): void
    {
        $appUrl = rtrim($this->config['app_url'] ?? '', '/');
        $link = $appUrl . '/tickets/' . $ticket['id'];

        $subject = '[T Solutions] Novo comentário no chamado #' . $ticket['id'];
        $body = $commentAuthor['name'] . " comentou no chamado #" . $ticket['id'] . ":\n\n";
        $body .= $commentBody . "\n\n";
        $body .= "Acesse: " . $link . "\n";

        $emails = [];
        if (!empty($ticket['requester_email'])) {
            $emails[] = $ticket['requester_email'];
        }
        if (!empty($ticket['agent_email'])) {
            $emails[] = $ticket['agent_email'];
        }

        $authorEmail = $commentAuthor['email'] ?? '';
        foreach (array_unique($emails) as $email) {
            if ($email && $email !== $authorEmail) {
                $this->send($email, $subject, $body);
            }
        }
    }

    /** @return string[] Emails de admins e agentes que recebem notificações */
    private function getNotificationRecipients(): array
    {
        $users = $this->userModel->findByRoles(['admin', 'agent', 'diretoria', 'suporte']);
        $emails = [];
        foreach ($users as $u) {
            if (!empty($u['email']) && ($u['is_active'] ?? true)) {
                $emails[] = $u['email'];
            }
        }
        return $emails;
    }
}
