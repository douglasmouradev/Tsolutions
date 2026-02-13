<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class TicketComment extends BaseModel
{
    protected function getTable(): string
    {
        return 'ticket_comments';
    }

    protected function getPrimaryKey(): string
    {
        return 'id';
    }

    public function findByTicketId(int $ticketId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.*, u.name AS author_name 
             FROM ticket_comments c 
             JOIN users u ON c.author_id = u.id 
             WHERE c.ticket_id = ? 
             ORDER BY c.created_at ASC'
        );
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll();
    }

    public function create(int $ticketId, int $authorId, string $body): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO ticket_comments (ticket_id, author_id, body) VALUES (?, ?, ?)'
        );
        $stmt->execute([$ticketId, $authorId, $body]);
        return (int) $this->pdo->lastInsertId();
    }
}
