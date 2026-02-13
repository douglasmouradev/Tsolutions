<?php
$pageTitle = 'Esqueci minha senha';
require __DIR__ . '/../partials/head.php';
?>
<div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow-sm" style="max-width: 400px; width: 100%;">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <img src="/assets/logo.png" alt="T Solutions" height="48" class="mb-2">
                <h2 class="card-title mb-0">Esqueci minha senha</h2>
            </div>
            <p class="text-muted small text-center mb-4">Informe seu e-mail para receber um link de redefinição de senha.</p>
            <?php require __DIR__ . '/../partials/flash.php'; ?>
            <form method="post" action="/forgot-password">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" name="email" id="email" class="form-control" required
                           value="<?= e($_POST['email'] ?? '') ?>" autocomplete="email">
                </div>
                <button type="submit" class="btn btn-primary w-100">Enviar link</button>
            </form>
            <div class="text-center mt-3">
                <a href="/login" class="text-muted small">Voltar ao login</a>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
