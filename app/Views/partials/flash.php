<?php
$success = $_SESSION['flash_success'] ?? null;
$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= e($success) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= e($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
