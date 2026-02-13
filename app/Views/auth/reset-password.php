<?php
$pageTitle = 'Redefinir senha';
require __DIR__ . '/../partials/head.php';
?>
<div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow-sm" style="max-width: 400px; width: 100%;">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <img src="/assets/logo.png" alt="T Solutions" height="48" class="mb-2">
                <h2 class="card-title mb-0">Nova senha</h2>
            </div>
            <?php require __DIR__ . '/../partials/flash.php'; ?>
            <form method="post" action="/reset-password">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= e($data['token'] ?? '') ?>">
                <div class="mb-3">
                    <label for="new_password" class="form-label">Nova senha</label>
                    <input type="password" name="new_password" id="new_password" class="form-control" required minlength="6" autocomplete="new-password">
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirmar senha</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary w-100">Redefinir senha</button>
            </form>
            <div class="text-center mt-3">
                <a href="/login" class="text-muted small">Voltar ao login</a>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
