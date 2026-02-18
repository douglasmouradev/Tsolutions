<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?>T Solutions</title>
    <link rel="icon" type="image/png" href="/assets/logo_titanium_2025.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body<?= isset($bodyClass) ? ' class="' . e($bodyClass) . '"' : '' ?>>
<?php if (isset($bodyClass) && $bodyClass === 'page-login'): ?>
<div class="logo-titanium-bar sticky-top">
    <a href="/" class="d-inline-block p-3"><img src="/assets/logo_titanium_grupo.png" alt="TITANIUM Grupo Corporation" class="logo-corporation" style="height:56px;max-width:220px;object-fit:contain;"></a>
</div>
<?php endif; ?>
