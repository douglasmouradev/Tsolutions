<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Ticket;
use App\Services\AuthService;

class DashboardController
{
    public function __construct(
        private Ticket $ticketModel,
        private AuthService $authService
    ) {
    }

    public function index(): void
    {
        $counts = $this->ticketModel->countByStatus();
        $lastTickets = $this->ticketModel->getLastUpdated(10);
        $currentUser = $this->authService->currentUser();
        require __DIR__ . '/../Views/dashboard/index.php';
    }
}
