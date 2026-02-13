<?php
$pageTitle = 'Editar técnico';
$currentUser = $currentUser ?? null;
$t = $tecnico;
if (!empty($t['data_nascimento'])) {
    $t['data_nascimento'] = date('Y-m-d', strtotime($t['data_nascimento']));
}
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/nav.php';
?>
<div class="container py-4">
    <h1 class="mb-4">Editar técnico #<?= e((string) $tecnico['id']) ?></h1>
    <?php require __DIR__ . '/../partials/flash.php'; ?>
    <form method="post" action="/tecnicos/<?= $tecnico['id'] ?>/update">
        <?= csrf_field() ?>
        <?php require __DIR__ . '/_form_fields.php'; ?>
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="/tecnicos" class="btn btn-outline-secondary">Voltar</a>
    </form>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
