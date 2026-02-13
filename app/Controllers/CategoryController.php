<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middlewares\Csrf;
use App\Models\Category;
use App\Services\AuthService;

class CategoryController
{
    public function __construct(
        private Category $categoryModel,
        private AuthService $authService
    ) {
    }

    public function index(): void
    {
        $categories = $this->categoryModel->all();
        $currentUser = $this->authService->currentUser();
        require __DIR__ . '/../Views/categories/index.php';
    }

    public function store(): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /categories');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        if (strlen($name) < 2) {
            $_SESSION['flash_error'] = 'Nome da categoria deve ter pelo menos 2 caracteres.';
            header('Location: /categories');
            exit;
        }

        if ($this->categoryModel->findByName($name)) {
            $_SESSION['flash_error'] = 'Categoria já existe.';
            header('Location: /categories');
            exit;
        }

        $this->categoryModel->create($name);
        $_SESSION['flash_success'] = 'Categoria criada.';
        header('Location: /categories');
        exit;
    }

    public function update(int $id): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /categories');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        if (strlen($name) < 2) {
            $_SESSION['flash_error'] = 'Nome inválido.';
            header('Location: /categories');
            exit;
        }

        $this->categoryModel->update($id, $name);
        $_SESSION['flash_success'] = 'Categoria atualizada.';
        header('Location: /categories');
        exit;
    }

    public function delete(int $id): void
    {
        if (!Csrf::validate()) {
            $_SESSION['flash_error'] = 'Token inválido.';
            header('Location: /categories');
            exit;
        }

        $this->categoryModel->delete($id);
        $_SESSION['flash_success'] = 'Categoria excluída.';
        header('Location: /categories');
        exit;
    }
}
