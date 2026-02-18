<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middlewares\Csrf;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketStatusHistory;
use App\Models\Attachment;
use App\Models\Category;
use App\Models\User;
use App\Models\Unidade;
use App\Services\AuthService;
use App\Services\NotificationService;
use App\Services\UploadService;

class TicketController
{
    private const PER_PAGE = 25;

    public function __construct(
        private Ticket $ticketModel,
        private TicketComment $commentModel,
        private TicketStatusHistory $statusHistoryModel,
        private Attachment $attachmentModel,
        private Category $categoryModel,
        private User $userModel,
        private Unidade $unidadeModel,
        private AuthService $authService,
        private UploadService $uploadService,
        private NotificationService $notificationService,
        private array $config
    ) {
    }

    public function index(): void
    {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'agent_id' => $_GET['agent_id'] ?? '',
            'requester_id' => $_GET['requester_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'sla_status' => $_GET['sla_status'] ?? '',
            'q' => $_GET['q'] ?? '',
        ];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $result = $this->ticketModel->search($filters, $page, self::PER_PAGE);

        $categories = $this->categoryModel->all();
        $agents = $this->userModel->findAllAgents();
        $requesters = $this->authService->hasRole(['admin', 'agent', 'diretoria', 'suporte']) ? $this->userModel->findAllRequesters() : [];
        $currentUser = $this->authService->currentUser();
        $authService = $this->authService;

        require __DIR__ . '/../Views/tickets/index.php';
    }

    public function create(): void
    {
        $categories = $this->categoryModel->all();
        $unidades = $this->unidadeModel->all('name');
        $user = $this->authService->currentUser();
        $requesterId = $user['id'];
        $currentUser = $user;
        require __DIR__ . '/../Views/tickets/create.php';
    }

    public function store(): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /tickets/create');
            exit;
        }

        $user = $this->authService->currentUser();
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $categoryId = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;
        $priority = $_POST['priority'] ?? 'baixa';
        $dueAt = null;
        $slaPrazo = $_POST['sla_prazo'] ?? 'prioridade';
        if ($slaPrazo === 'personalizado' && !empty($_POST['due_at'])) {
            $dueAt = date('Y-m-d H:i:s', strtotime($_POST['due_at']));
        } elseif (in_array($slaPrazo, ['nbd', 'd1', 'd2'], true)) {
            $dias = $slaPrazo === 'nbd' || $slaPrazo === 'd1' ? 1 : 2;
            $dueAt = $this->addBusinessDays(time(), $dias);
        } elseif (is_numeric($slaPrazo) && (int) $slaPrazo > 0) {
            $hours = (int) $slaPrazo;
            $dueAt = date('Y-m-d H:i:s', time() + $hours * 3600);
        } elseif ($slaPrazo === 'prioridade' && !empty($this->config['sla_hours'][$priority] ?? null)) {
            $hours = (int) $this->config['sla_hours'][$priority];
            $dueAt = date('Y-m-d H:i:s', time() + $hours * 3600);
        }

        $errors = [];
        if (strlen($title) < 3) {
            $errors[] = 'Título deve ter pelo menos 3 caracteres.';
        }
        $allowedPriorities = ['baixa', 'media', 'alta', 'critica'];
        if (!in_array($priority, $allowedPriorities, true)) {
            $errors[] = 'Prioridade inválida.';
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            $_SESSION['old'] = $_POST;
            header('Location: /tickets/create');
            exit;
        }

        $planilha = $this->extractPlanilhaFromPost($_POST);
        $dataPostagem = !empty($_POST['data_postagem']) ? $_POST['data_postagem'] : null;
        $dataVencCh = !empty($_POST['data_vencimento_ch']) ? $_POST['data_vencimento_ch'] : null;
        $dataDisp = !empty($_POST['data_disponibilidade']) ? $_POST['data_disponibilidade'] : null;
        $horaDisp = !empty($_POST['hora_disponibilidade']) ? $_POST['hora_disponibilidade'] : null;
        $dataAtendimento = !empty($_POST['data_atendimento']) ? $_POST['data_atendimento'] : null;
        $horaAtendimento = !empty($_POST['hora_atendimento']) ? $_POST['hora_atendimento'] : null;
        $valorTecnico = isset($_POST['valor_tecnico']) && $_POST['valor_tecnico'] !== '' ? (float) str_replace(',', '.', $_POST['valor_tecnico']) : null;
        $modalidadeTecnico = !empty($_POST['modalidade_tecnico']) ? trim($_POST['modalidade_tecnico']) : null;

        $id = $this->ticketModel->create(array_merge([
            'title' => $title,
            'description' => $description ?: null,
            'requester_id' => $user['id'],
            'category_id' => $categoryId,
            'priority' => $priority,
            'due_at' => $dueAt,
        ], $planilha, [
            'data_postagem' => $dataPostagem,
            'data_vencimento_ch' => $dataVencCh,
            'data_disponibilidade' => $dataDisp,
            'hora_disponibilidade' => $horaDisp,
            'data_atendimento' => $dataAtendimento,
            'hora_atendimento' => $horaAtendimento,
            'valor_tecnico' => $valorTecnico,
            'modalidade_tecnico' => $modalidadeTecnico,
        ]));

        if (!empty($_FILES['attachments']['name'])) {
            $files = $this->normalizeFiles($_FILES['attachments']);
            foreach ($files as $file) {
                $result = $this->uploadService->store($file, $id, $user['id']);
                if ($result) {
                    $this->attachmentModel->create([
                        'ticket_id' => $id,
                        'uploaded_by' => $user['id'],
                        'stored_name' => $result['stored_name'],
                        'original_name' => $result['original_name'],
                        'mime_type' => $result['mime_type'],
                        'size_bytes' => $result['size_bytes'],
                    ]);
                }
            }
        }

        $this->log('ticket_create', ['ticket_id' => $id, 'user_id' => $user['id']]);

        $ticket = $this->ticketModel->findWithRelations($id);
        if ($ticket) {
            $this->notificationService->notifyTicketCreated($ticket, $user);
        }

        $_SESSION['flash_success'] = 'Chamado criado com sucesso.';
        header('Location: /tickets/' . $id);
        exit;
    }

    public function show(int $id): void
    {
        $ticket = $this->ticketModel->findWithRelations($id);
        if (!$ticket) {
            $_SESSION['flash_error'] = 'Chamado não encontrado.';
            header('Location: /tickets');
            exit;
        }

        if (!$this->authService->canViewTicket($ticket)) {
            $_SESSION['flash_error'] = 'Acesso negado a este chamado.';
            header('Location: /tickets');
            exit;
        }

        $comments = $this->commentModel->findByTicketId($id);
        $attachments = $this->attachmentModel->findByTicketId($id);
        $statusHistory = $this->statusHistoryModel->findByTicketId($id);
        $agents = $this->userModel->findAllAgents();
        $categories = $this->categoryModel->all();
        $currentUser = $this->authService->currentUser();
        $canManage = $this->authService->canManageTicket($ticket);
        $canClose = $this->authService->canCloseTicket($ticket);

        require __DIR__ . '/../Views/tickets/show.php';
    }

    public function edit(int $id): void
    {
        $ticket = $this->ticketModel->findWithRelations($id);
        if (!$ticket) {
            $_SESSION['flash_error'] = 'Chamado não encontrado.';
            header('Location: /tickets');
            exit;
        }

        if (!$this->authService->canManageTicket($ticket)) {
            $_SESSION['flash_error'] = 'Acesso negado.';
            header('Location: /tickets');
            exit;
        }

        $categories = $this->categoryModel->all();
        $unidades = $this->unidadeModel->all('name');
        $agents = $this->userModel->findAllAgents();
        $currentUser = $this->authService->currentUser();

        require __DIR__ . '/../Views/tickets/edit.php';
    }

    public function update(int $id): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /tickets/' . $id . '/edit');
            exit;
        }

        $ticket = $this->ticketModel->find($id);
        if (!$ticket || !$this->authService->canManageTicket($ticket)) {
            $_SESSION['flash_error'] = 'Acesso negado.';
            header('Location: /tickets');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $categoryId = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;
        $priority = $_POST['priority'] ?? 'baixa';
        $agentId = !empty($_POST['agent_id']) ? (int) $_POST['agent_id'] : null;
        $dueAt = null;
        $slaPrazo = $_POST['sla_prazo'] ?? 'personalizado';
        if (in_array($slaPrazo, ['nbd', 'd1', 'd2'], true)) {
            $dias = $slaPrazo === 'nbd' || $slaPrazo === 'd1' ? 1 : 2;
            $dueAt = $this->addBusinessDays(time(), $dias);
        } elseif (is_numeric($slaPrazo) && (int) $slaPrazo > 0) {
            $hours = (int) $slaPrazo;
            $dueAt = date('Y-m-d H:i:s', time() + $hours * 3600);
        } elseif ($slaPrazo === 'prioridade' && !empty($this->config['sla_hours'][$priority] ?? null)) {
            $hours = (int) $this->config['sla_hours'][$priority];
            $dueAt = date('Y-m-d H:i:s', time() + $hours * 3600);
        } elseif (!empty($_POST['due_at'])) {
            $dueAt = date('Y-m-d H:i:s', strtotime($_POST['due_at']));
        }

        $errors = [];
        if (strlen($title) < 3) {
            $errors[] = 'Título deve ter pelo menos 3 caracteres.';
        }
        $allowedPriorities = ['baixa', 'media', 'alta', 'critica'];
        if (!in_array($priority, $allowedPriorities, true)) {
            $errors[] = 'Prioridade inválida.';
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            header('Location: /tickets/' . $id . '/edit');
            exit;
        }

        $planilha = $this->extractPlanilhaFromPost($_POST);
        $planilha['data_postagem'] = !empty($_POST['data_postagem']) ? $_POST['data_postagem'] : null;
        $planilha['data_vencimento_ch'] = !empty($_POST['data_vencimento_ch']) ? $_POST['data_vencimento_ch'] : null;
        $planilha['data_disponibilidade'] = !empty($_POST['data_disponibilidade']) ? $_POST['data_disponibilidade'] : null;
        $planilha['hora_disponibilidade'] = !empty($_POST['hora_disponibilidade']) ? $_POST['hora_disponibilidade'] : null;
        $planilha['data_atendimento'] = !empty($_POST['data_atendimento']) ? $_POST['data_atendimento'] : null;
        $planilha['hora_atendimento'] = !empty($_POST['hora_atendimento']) ? $_POST['hora_atendimento'] : null;
        $user = $this->authService->currentUser();
        if (in_array($user['role'] ?? '', ['admin', 'agent', 'diretoria', 'suporte'], true)) {
            $planilha['valor_tecnico'] = isset($_POST['valor_tecnico']) && $_POST['valor_tecnico'] !== '' ? (float) str_replace(',', '.', $_POST['valor_tecnico']) : null;
            $planilha['modalidade_tecnico'] = !empty($_POST['modalidade_tecnico']) ? trim($_POST['modalidade_tecnico']) : null;
        } else {
            unset($planilha['valor_tecnico'], $planilha['modalidade_tecnico']);
        }

        $this->ticketModel->update($id, array_merge([
            'title' => $title,
            'description' => $description ?: null,
            'category_id' => $categoryId,
            'priority' => $priority,
            'agent_id' => $agentId,
            'due_at' => $dueAt,
        ], $planilha));

        $this->log('ticket_edit', ['ticket_id' => $id, 'user_id' => $user['id']]);
        $_SESSION['flash_success'] = 'Chamado atualizado.';
        header('Location: /tickets/' . $id);
        exit;
    }

    private function normalizeFiles(array $files): array
    {
        $out = [];
        $names = $files['name'] ?? [];
        if (!is_array($names)) {
            $names = [$names];
        }
        foreach (array_keys($names) as $i) {
            if (empty($files['name'][$i])) {
                continue;
            }
            $out[] = [
                'name' => $files['name'][$i] ?? '',
                'type' => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i] ?? '',
                'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$i] ?? 0,
            ];
        }
        return $out;
    }

    private function extractPlanilhaFromPost(array $post): array
    {
        $keys = [
            'cliente_raiz', 'cliente', 'sigla_unidade_loja_ag', 'tipo_contrato', 'tipo_ch', 'contrato_baseline',
            'sla', 'reversa', 'codigo_postagem', 'equipamento', 'n_serie', 'patrimonio', 'hostname',
            'usuario_contato', 'telefone_usuario', 'email_usuario', 'nome_solicitante_ch', 'numero_ch', 'moebius',
            'n_cl', 'n_tarefa_remessa', 'endereco', 'bairro', 'cidade', 'uf', 'cep',
            'operacao', 'intercorrencias', 'observacao_tecnico',
            'nome_tecnico', 'cpf_tecnico', 'rg_tecnico', 'valor_tecnico', 'modalidade_tecnico',
        ];
        $out = [];
        foreach ($keys as $k) {
            $out[$k] = isset($post[$k]) ? trim((string) $post[$k]) : '';
        }
        return $out;
    }

    public function changeStatus(int $id): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /tickets/' . $id);
            exit;
        }

        $ticket = $this->ticketModel->find($id);
        if (!$ticket) {
            $_SESSION['flash_error'] = 'Chamado não encontrado.';
            header('Location: /tickets');
            exit;
        }

        $newStatus = $_POST['status'] ?? '';
        $canClose = in_array($newStatus, ['fechado', 'cancelado'], true);
        $allowed = $canClose ? $this->authService->canCloseTicket($ticket) : $this->authService->canManageTicket($ticket);
        if (!$allowed) {
            $_SESSION['flash_error'] = 'Sem permissão para alterar status.';
            header('Location: /tickets/' . $id);
            exit;
        }

        $note = trim($_POST['note'] ?? '');
        $allowed = ['aberto', 'em_andamento', 'fechado', 'cancelado'];
        if (!in_array($newStatus, $allowed, true)) {
            $_SESSION['flash_error'] = 'Status inválido.';
            header('Location: /tickets/' . $id);
            exit;
        }

        $user = $this->authService->currentUser();
        $oldStatus = $ticket['status'];

        $updateData = ['status' => $newStatus];
        if ($newStatus === 'fechado' || $newStatus === 'cancelado') {
            $updateData['closed_at'] = date('Y-m-d H:i:s');
        } elseif (in_array($newStatus, ['aberto', 'em_andamento'], true)) {
            $updateData['closed_at'] = null;
        }

        $this->ticketModel->update($id, $updateData);
        $this->statusHistoryModel->create($id, $oldStatus, $newStatus, $user['id'], $note ?: null);

        $this->log('ticket_status_change', ['ticket_id' => $id, 'old' => $oldStatus, 'new' => $newStatus]);
        $_SESSION['flash_success'] = 'Status alterado com sucesso.';
        header('Location: /tickets/' . $id);
        exit;
    }

    public function delete(int $id): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /tickets');
            exit;
        }

        $ticket = $this->ticketModel->find($id);
        if (!$ticket) {
            $_SESSION['flash_error'] = 'Chamado não encontrado.';
            header('Location: /tickets');
            exit;
        }

        if (!$this->authService->canManageTicket($ticket)) {
            $_SESSION['flash_error'] = 'Sem permissão para excluir este chamado.';
            header('Location: /tickets');
            exit;
        }

        $this->ticketModel->delete($id);
        $this->log('ticket_delete', ['ticket_id' => $id]);
        $_SESSION['flash_success'] = 'Chamado excluído.';
        header('Location: /tickets');
        exit;
    }

    public function assign(int $id): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /tickets/' . $id);
            exit;
        }

        $ticket = $this->ticketModel->find($id);
        if (!$ticket || !$this->authService->canManageTicket($ticket)) {
            $_SESSION['flash_error'] = 'Sem permissão.';
            header('Location: /tickets/' . $id);
            exit;
        }

        if (!$this->authService->hasRole(['admin', 'agent', 'diretoria', 'suporte'])) {
            $_SESSION['flash_error'] = 'Apenas agentes podem atribuir chamados.';
            header('Location: /tickets/' . $id);
            exit;
        }

        $agentId = !empty($_POST['agent_id']) ? (int) $_POST['agent_id'] : null;
        $this->ticketModel->update($id, ['agent_id' => $agentId]);

        if ($agentId) {
            $ticket = $this->ticketModel->findWithRelations($id);
            $agent = $this->userModel->find($agentId);
            if ($ticket && $agent) {
                $this->notificationService->notifyTicketAssigned($ticket, $agent);
            }
        }

        $_SESSION['flash_success'] = $agentId ? 'Agente atribuído.' : 'Atribuição removida.';
        header('Location: /tickets/' . $id);
        exit;
    }

    public function addComment(int $id): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /tickets/' . $id);
            exit;
        }

        $ticket = $this->ticketModel->find($id);
        if (!$ticket || !$this->authService->canManageTicket($ticket)) {
            $_SESSION['flash_error'] = 'Sem permissão para comentar.';
            header('Location: /tickets/' . $id);
            exit;
        }

        $body = trim($_POST['body'] ?? '');
        if (strlen($body) < 1) {
            $_SESSION['flash_error'] = 'Comentário não pode estar vazio.';
            header('Location: /tickets/' . $id);
            exit;
        }

        $user = $this->authService->currentUser();
        $this->commentModel->create($id, $user['id'], $body);

        $ticket = $this->ticketModel->findWithRelations($id);
        if ($ticket) {
            $this->notificationService->notifyCommentAdded($ticket, $user, $body);
        }

        $_SESSION['flash_success'] = 'Comentário adicionado.';
        header('Location: /tickets/' . $id);
        exit;
    }

    public function uploadAttachment(int $id): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /tickets/' . $id);
            exit;
        }

        $ticket = $this->ticketModel->find($id);
        if (!$ticket || !$this->authService->canManageTicket($ticket)) {
            $_SESSION['flash_error'] = 'Sem permissão.';
            header('Location: /tickets/' . $id);
            exit;
        }

        if (empty($_FILES['attachment']['name'])) {
            $_SESSION['flash_error'] = 'Selecione um arquivo.';
            header('Location: /tickets/' . $id);
            exit;
        }

        $result = $this->uploadService->store($_FILES['attachment'], $id, $this->authService->currentUser()['id']);
        if (!$result) {
            $_SESSION['flash_error'] = 'Falha no upload. Verifique tipo e tamanho do arquivo.';
            header('Location: /tickets/' . $id);
            exit;
        }

        $this->attachmentModel->create([
            'ticket_id' => $id,
            'uploaded_by' => $this->authService->currentUser()['id'],
            'stored_name' => $result['stored_name'],
            'original_name' => $result['original_name'],
            'mime_type' => $result['mime_type'],
            'size_bytes' => $result['size_bytes'],
        ]);

        $this->log('attachment_upload', ['ticket_id' => $id]);
        $_SESSION['flash_success'] = 'Anexo enviado com sucesso.';
        header('Location: /tickets/' . $id);
        exit;
    }

    public function downloadAttachment(int $ticketId, int $attachmentId): void
    {
        $ticket = $this->ticketModel->find($ticketId);
        if (!$ticket || !$this->authService->canViewTicket($ticket)) {
            http_response_code(403);
            echo 'Acesso negado.';
            exit;
        }

        $attachment = $this->attachmentModel->find($attachmentId);
        if (!$attachment || (int) $attachment['ticket_id'] !== $ticketId) {
            http_response_code(404);
            echo 'Anexo não encontrado.';
            exit;
        }

        $path = $this->uploadService->getFilePath($attachment['stored_name']);
        if (!file_exists($path)) {
            http_response_code(404);
            echo 'Arquivo não encontrado.';
            exit;
        }

        header('Content-Type: ' . $attachment['mime_type']);
        header('Content-Disposition: attachment; filename="' . str_replace('"', '\\"', $attachment['original_name']) . '"');
        header('Content-Length: ' . $attachment['size_bytes']);
        readfile($path);
        exit;
    }

    public function viewAttachment(int $ticketId, int $attachmentId): void
    {
        $ticket = $this->ticketModel->find($ticketId);
        if (!$ticket || !$this->authService->canViewTicket($ticket)) {
            http_response_code(403);
            echo 'Acesso negado.';
            exit;
        }

        $attachment = $this->attachmentModel->find($attachmentId);
        if (!$attachment || (int) $attachment['ticket_id'] !== $ticketId) {
            http_response_code(404);
            echo 'Anexo não encontrado.';
            exit;
        }

        $path = $this->uploadService->getFilePath($attachment['stored_name']);
        if (!file_exists($path)) {
            http_response_code(404);
            echo 'Arquivo não encontrado.';
            exit;
        }

        header('Content-Type: ' . $attachment['mime_type']);
        header('Content-Disposition: inline; filename="' . str_replace('"', '\\"', $attachment['original_name']) . '"');
        header('Content-Length: ' . $attachment['size_bytes']);
        readfile($path);
        exit;
    }

    /** Adiciona N dias úteis (exclui sábado e domingo) e retorna data/hora fim do dia (18:00). */
    private function addBusinessDays(int $timestamp, int $days): string
    {
        $date = new \DateTime();
        $date->setTimestamp($timestamp);
        $added = 0;
        while ($added < $days) {
            $date->modify('+1 day');
            $dow = (int) $date->format('w'); // 0=domingo, 6=sábado
            if ($dow !== 0 && $dow !== 6) {
                $added++;
            }
        }
        $date->setTime(18, 0, 0);
        return $date->format('Y-m-d H:i:s');
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
