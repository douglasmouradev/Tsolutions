<?php
$currentUser = $currentUser ?? null;
$config = require __DIR__ . '/../../../config/config.php';
$baseUrl = $config['app_url'] ?? '';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="logo-titanium-nav">
        <a href="/" class="d-inline-block py-2 px-3">
            <img src="/assets/logo_titanium_grupo.png" alt="TITANIUM Grupo Corporation" class="logo-corporation" style="height:48px;max-width:200px;object-fit:contain;">
        </a>
    </div>
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="/dashboard">
            <img src="/assets/logo.png" alt="T Solutions" height="32">
            <span>T Solutions</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/dashboard"><i class="bi bi-grid"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/tickets"><i class="bi bi-ticket"></i> Chamados</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/reports"><i class="bi bi-graph-up"></i> Relatórios</a>
                </li>
                <?php if ($currentUser && in_array($currentUser['role'], ['admin'], true)): ?>
                <li class="nav-item">
                    <a class="nav-link" href="/categories"><i class="bi bi-tags"></i> Categorias</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/users"><i class="bi bi-people"></i> Usuários</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/tecnicos"><i class="bi bi-person-gear"></i> Cadastro Técnicos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/unidades"><i class="bi bi-building"></i> Cadastro Unidade</a>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if ($currentUser): ?>
                <li class="nav-item">
                    <span class="nav-link"><?= e($currentUser['name']) ?> (<?= e($currentUser['role']) ?>)</span>
                </li>
                <li class="nav-item">
                    <form method="post" action="/logout" class="d-inline">
                        <?= \App\Middlewares\Csrf::field() ?>
                        <button type="submit" class="btn btn-outline-light btn-sm">Sair</button>
                    </form>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
