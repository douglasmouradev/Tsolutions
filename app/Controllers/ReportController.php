<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Ticket;
use App\Models\Category;
use App\Models\User;
use App\Services\AuthService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReportController
{
    private const EXPORT_LIMIT = 5000;

    public function __construct(
        private Ticket $ticketModel,
        private Category $categoryModel,
        private User $userModel,
        private AuthService $authService
    ) {
    }

    public function index(): void
    {
        $categories = $this->categoryModel->all();
        $agents = $this->authService->hasRole(['admin', 'agent', 'diretoria', 'suporte']) ? $this->userModel->findAllAgents() : [];
        $requesters = $this->authService->hasRole(['admin', 'agent', 'diretoria', 'suporte']) ? $this->userModel->findAllRequesters() : [];
        $currentUser = $this->authService->currentUser();
        require __DIR__ . '/../Views/reports/index.php';
    }

    public function exportExcel(): void
    {
        $filters = $this->buildFilters();
        $result = $this->ticketModel->search($filters, 1, self::EXPORT_LIMIT);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Chamados');

        $headers = [
            'ID', 'Título', 'Status', 'Prioridade', 'Categoria', 'Solicitante', 'Agente',
            'Cliente', 'Cliente raiz', 'Equipamento', 'Nº Série', 'Endereço', 'Cidade', 'UF',
            'N° CH', 'Criado em', 'Atualizado em', 'Vencimento',
            'Nome do técnico', 'Valor do técnico', 'Modalidade',
        ];
        $columns = range('A', 'U');
        foreach ($headers as $i => $h) {
            $sheet->setCellValue($columns[$i] . '1', $h);
        }

        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:U1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($result['items'] as $t) {
            $sheet->setCellValue('A' . $row, $t['id']);
            $sheet->setCellValue('B' . $row, $t['title'] ?? '');
            $sheet->setCellValue('C' . $row, statusLabel($t['status'] ?? null));
            $sheet->setCellValue('D' . $row, $t['priority'] ?? '');
            $sheet->setCellValue('E' . $row, $t['category_name'] ?? '');
            $sheet->setCellValue('F' . $row, $t['requester_name'] ?? '');
            $sheet->setCellValue('G' . $row, $t['agent_name'] ?? '');
            $sheet->setCellValue('H' . $row, $t['cliente'] ?? '');
            $sheet->setCellValue('I' . $row, $t['cliente_raiz'] ?? '');
            $sheet->setCellValue('J' . $row, $t['equipamento'] ?? '');
            $sheet->setCellValue('K' . $row, $t['n_serie'] ?? '');
            $sheet->setCellValue('L' . $row, $t['endereco'] ?? '');
            $sheet->setCellValue('M' . $row, $t['cidade'] ?? '');
            $sheet->setCellValue('N' . $row, $t['uf'] ?? '');
            $sheet->setCellValue('O' . $row, $t['numero_ch'] ?? '');
            $sheet->setCellValue('P' . $row, $this->fmtDate($t['created_at'] ?? null));
            $sheet->setCellValue('Q' . $row, $this->fmtDate($t['updated_at'] ?? $t['created_at'] ?? null));
            $sheet->setCellValue('R' . $row, $this->fmtDate($t['due_at'] ?? null));
            $sheet->setCellValue('S' . $row, $t['nome_tecnico'] ?? '');
            $sheet->setCellValue('T' . $row, isset($t['valor_tecnico']) && $t['valor_tecnico'] !== '' && $t['valor_tecnico'] !== null ? 'R$ ' . number_format((float) $t['valor_tecnico'], 2, ',', '.') : '');
            $sheet->setCellValue('U' . $row, $t['modalidade_tecnico'] ?? '');
            $row++;
        }

        foreach (range('A', 'U') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $filename = 'relatorio_chamados_' . date('Y-m-d_H-i') . '.xlsx';

        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Content-Length: ' . filesize($tempFile));

        readfile($tempFile);
        @unlink($tempFile);
        exit;
    }

    public function exportPdf(): void
    {
        $filters = $this->buildFilters();
        $result = $this->ticketModel->search($filters, 1, self::EXPORT_LIMIT);

        $html = $this->buildPdfHtml($result['items'], $result['total']);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'relatorio_chamados_' . date('Y-m-d_H-i') . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        echo $dompdf->output();
        exit;
    }

    private function buildFilters(): array
    {
        $user = $this->authService->currentUser();
        $filters = [
            'status' => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'agent_id' => $_GET['agent_id'] ?? '',
            'requester_id' => $_GET['requester_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'q' => $_GET['q'] ?? '',
        ];
        if (in_array($user['role'], ['requester', 'externo'], true)) {
            $filters['requester_id'] = (string) $user['id'];
        }
        return $filters;
    }

    private function fmtDate(?string $dt): string
    {
        if (!$dt) {
            return '';
        }
        try {
            $d = new \DateTime($dt);
            return $d->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            return $dt;
        }
    }

    private function buildPdfHtml(array $items, int $total): string
    {
        $rows = '';
        foreach ($items as $t) {
            $rows .= '<tr>';
            $rows .= '<td>' . htmlspecialchars((string) ($t['id'] ?? '')) . '</td>';
            $rows .= '<td>' . htmlspecialchars($t['title'] ?? '') . '</td>';
            $rows .= '<td>' . htmlspecialchars(statusLabel($t['status'] ?? null)) . '</td>';
            $rows .= '<td>' . htmlspecialchars($t['priority'] ?? '') . '</td>';
            $rows .= '<td>' . htmlspecialchars($t['category_name'] ?? '') . '</td>';
            $rows .= '<td>' . htmlspecialchars($t['requester_name'] ?? '') . '</td>';
            $rows .= '<td>' . htmlspecialchars($t['agent_name'] ?? '') . '</td>';
            $rows .= '<td>' . htmlspecialchars($t['cliente'] ?? '') . '</td>';
            $rows .= '<td>' . $this->fmtDate($t['created_at'] ?? null) . '</td>';
            $rows .= '</tr>';
        }

        return '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
h1 { font-size: 14px; }
table { width: 100%; border-collapse: collapse; }
th, td { border: 1px solid #333; padding: 4px; }
th { background: #4472C4; color: white; }
</style>
</head>
<body>
<h1>Relatório de Chamados - T Solutions</h1>
<p>Gerado em: ' . date('d/m/Y H:i') . ' | Total: ' . $total . ' registro(s)</p>
<table>
<thead>
<tr>
<th>ID</th><th>Título</th><th>Status</th><th>Prioridade</th><th>Categoria</th>
<th>Solicitante</th><th>Agente</th><th>Cliente</th><th>Criado em</th>
</tr>
</thead>
<tbody>' . $rows . '</tbody>
</table>
</body>
</html>';
    }
}
