<?php require APPROOT . '/app/views/layout/header.php'; ?>
<div class="auth-wrapper">
    <div class="auth-card" aria-labelledby="login-title">
        <h1 class="auth-title" id="login-title">Bem-vindo</h1>
        <p class="auth-subtitle">Inicie sessão para aceder à plataforma.</p>

        <?php if (!empty($data['general_err'])): ?>
            <div class="alert alert-danger" role="alert"><?php echo e($data['general_err']); ?></div>
        <?php endif; ?>
        <?php if (!empty($data['redirect'])): ?>
            <div class="alert alert-info" role="status">Após entrar será redirecionado para o formulário.</div>
        <?php endif; ?>

        <form action="<?php echo e(URLROOT); ?>/login" method="post" id="loginForm">
<?php echo csrf_field(); ?>
<input type="hidden" name="redirect" value="<?php echo e($data['redirect'] ?? ''); ?>">

            <div class="mb-3">
                <label class="form-label" for="login-email"><i class="fa-solid fa-envelope" aria-hidden="true"></i> Email</label>
                <input id="login-email" type="email" name="email" required autocomplete="email" autofocus
                    class="form-control <?php echo !empty($data['email_err']) ? 'is-invalid' : ''; ?>"
                    value="<?php echo e($data['email'] ?? ''); ?>"
                    aria-invalid="<?php echo !empty($data['email_err']) ? 'true' : 'false'; ?>"
                    <?php echo !empty($data['email_err']) ? 'aria-describedby="login-email-error"' : ''; ?>>
                <?php if (!empty($data['email_err'])): ?><div class="invalid-feedback" id="login-email-error"><?php echo e($data['email_err']); ?></div><?php endif; ?>
            </div>

            <div class="mb-4">
                <label class="form-label" for="login-password"><i class="fa-solid fa-lock" aria-hidden="true"></i> Palavra-passe</label>
                <div class="input-group">
                    <input id="login-password" type="password" name="password" required autocomplete="current-password"
                        class="form-control <?php echo !empty($data['password_err']) ? 'is-invalid' : ''; ?>"
                        aria-invalid="<?php echo !empty($data['password_err']) ? 'true' : 'false'; ?>">
                    <button class="btn btn-secondary" type="button" data-password-toggle="login-password" aria-label="Mostrar palavra-passe"><i class="fa-solid fa-eye" aria-hidden="true"></i></button>
                </div>
                <?php if (!empty($data['password_err'])): ?><div class="invalid-feedback d-block"><?php echo e($data['password_err']); ?></div><?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> Entrar</button>
        </form>

        <div class="auth-divider"><span>ou</span></div>
        <p class="text-center mb-0">Não tem conta? <a href="<?php echo e(URLROOT); ?>/register<?php echo !empty($data['redirect']) ? '?redirect=' . urlencode($data['redirect']) : ''; ?>">Registar-se</a></p>
    </div>
</div>
<script>
document.querySelector('[data-password-toggle]')?.addEventListener('click', function () {
    const input = document.getElementById(this.dataset.passwordToggle);
    const visible = input.type === 'text';
    input.type = visible ? 'password' : 'text';
    this.setAttribute('aria-label', visible ? 'Mostrar palavra-passe' : 'Ocultar palavra-passe');
    this.querySelector('i')?.classList.toggle('fa-eye-slash', !visible);
});
</script>
<?php require APPROOT . '/app/views/layout/footer.php'; ?>
