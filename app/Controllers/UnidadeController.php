<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middlewares\Csrf;
use App\Models\Unidade;
use App\Services\AuthService;

class UnidadeController
{
    public function __construct(
        private Unidade $unidadeModel,
        private AuthService $authService
    ) {
    }

    public function index(): void
    {
        $filtro = trim($_GET['q'] ?? '');
        $unidades = $filtro !== '' ? $this->unidadeModel->search($filtro) : $this->unidadeModel->all('name');
        $currentUser = $this->authService->currentUser();
        require __DIR__ . '/../Views/unidades/index.php';
    }

    public function store(): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /unidades');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $sigla = trim($_POST['sigla'] ?? '') ?: null;
        $endereco = trim($_POST['endereco'] ?? '') ?: null;
        $bairro = trim($_POST['bairro'] ?? '') ?: null;
        $cidade = trim($_POST['cidade'] ?? '') ?: null;
        $uf = trim($_POST['uf'] ?? '') ?: null;
        $cep = trim($_POST['cep'] ?? '') ?: null;
        $centroDeLucro = trim($_POST['centro_de_lucro'] ?? '') ?: null;

        if (strlen($name) < 2) {
            $_SESSION['flash_error'] = 'Nome deve ter pelo menos 2 caracteres.';
            header('Location: /unidades');
            exit;
        }

        $this->unidadeModel->create([
            'name' => $name,
            'sigla' => $sigla,
            'endereco' => $endereco,
            'bairro' => $bairro,
            'cidade' => $cidade,
            'uf' => $uf,
            'cep' => $cep,
            'centro_de_lucro' => $centroDeLucro,
        ]);
        $_SESSION['flash_success'] = 'Unidade cadastrada.';
        header('Location: /unidades');
        exit;
    }

    public function update(int $id): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /unidades');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $sigla = trim($_POST['sigla'] ?? '') ?: null;
        $endereco = trim($_POST['endereco'] ?? '') ?: null;
        $bairro = trim($_POST['bairro'] ?? '') ?: null;
        $cidade = trim($_POST['cidade'] ?? '') ?: null;
        $uf = trim($_POST['uf'] ?? '') ?: null;
        $cep = trim($_POST['cep'] ?? '') ?: null;
        $centroDeLucro = trim($_POST['centro_de_lucro'] ?? '') ?: null;

        if (strlen($name) < 2) {
            $_SESSION['flash_error'] = 'Nome inválido.';
            header('Location: /unidades');
            exit;
        }

        $this->unidadeModel->update($id, [
            'name' => $name,
            'sigla' => $sigla,
            'endereco' => $endereco,
            'bairro' => $bairro,
            'cidade' => $cidade,
            'uf' => $uf,
            'cep' => $cep,
            'centro_de_lucro' => $centroDeLucro,
        ]);
        $_SESSION['flash_success'] = 'Unidade atualizada.';
        header('Location: /unidades');
        exit;
    }

    public function delete(int $id): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /unidades');
            exit;
        }

        $this->unidadeModel->delete($id);
        $_SESSION['flash_success'] = 'Unidade excluída.';
        header('Location: /unidades');
        exit;
    }
}
