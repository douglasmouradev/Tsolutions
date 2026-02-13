<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middlewares\Csrf;
use App\Models\Tecnico;
use App\Services\AuthService;

class TecnicoController
{
    private const FIELDS = [
        'name', 'naturalidade', 'email', 'rg', 'cpf', 'data_nascimento', 'genero',
        'nome_mae', 'nome_pai', 'cep', 'endereco', 'numero', 'bairro', 'cidade', 'estado', 'pais',
        'celular_1', 'celular_2', 'whats', 'telefone_fixo', 'telefone',
        'referencia_bancaria', 'chave_pix', 'banco', 'cod_banco', 'agencia', 'conta', 'tipo_conta', 'operacao', 'favorecido',
        'razao_social', 'nome_fantasia', 'cnpj', 'inscricao_estadual', 'inscricao_municipal',
        'empresa_cep', 'empresa_endereco', 'empresa_numero', 'empresa_bairro', 'empresa_cidade', 'empresa_estado', 'empresa_pais',
        'empresa_referencia_bancaria', 'empresa_chave_pix', 'empresa_banco', 'empresa_cod_banco', 'empresa_agencia', 'empresa_conta', 'empresa_tipo_conta', 'empresa_operacao', 'empresa_favorecido',
    ];

    public function __construct(
        private Tecnico $tecnicoModel,
        private AuthService $authService
    ) {
    }

    public function index(): void
    {
        $filtro = trim($_GET['q'] ?? '');
        $tecnicos = $filtro !== '' ? $this->tecnicoModel->search($filtro) : $this->tecnicoModel->all('name');
        $currentUser = $this->authService->currentUser();
        require __DIR__ . '/../Views/tecnicos/index.php';
    }

    public function edit(int $id): void
    {
        $tecnico = $this->tecnicoModel->find($id);
        if (!$tecnico) {
            $_SESSION['flash_error'] = 'Técnico não encontrado.';
            header('Location: /tecnicos');
            exit;
        }
        $currentUser = $this->authService->currentUser();
        require __DIR__ . '/../Views/tecnicos/edit.php';
    }

    public function store(): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /tecnicos');
            exit;
        }

        $data = $this->extractFromPost($_POST);
        if (strlen(trim($data['name'] ?? '')) < 2) {
            $_SESSION['flash_error'] = 'Nome deve ter pelo menos 2 caracteres.';
            $_SESSION['old'] = $_POST;
            header('Location: /tecnicos');
            exit;
        }

        $this->tecnicoModel->create($data);
        $_SESSION['flash_success'] = 'Técnico cadastrado.';
        header('Location: /tecnicos');
        exit;
    }

    public function update(int $id): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /tecnicos');
            exit;
        }

        $data = $this->extractFromPost($_POST);
        if (strlen(trim($data['name'] ?? '')) < 2) {
            $_SESSION['flash_error'] = 'Nome inválido.';
            header('Location: /tecnicos/' . $id . '/edit');
            exit;
        }

        $this->tecnicoModel->update($id, $data);
        $_SESSION['flash_success'] = 'Técnico atualizado.';
        header('Location: /tecnicos');
        exit;
    }

    public function delete(int $id): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /tecnicos');
            exit;
        }

        $this->tecnicoModel->delete($id);
        $_SESSION['flash_success'] = 'Técnico excluído.';
        header('Location: /tecnicos');
        exit;
    }

    private function extractFromPost(array $post): array
    {
        $out = [];
        foreach (self::FIELDS as $f) {
            $out[$f] = isset($post[$f]) ? trim((string) $post[$f]) : '';
        }
        return $out;
    }
}
