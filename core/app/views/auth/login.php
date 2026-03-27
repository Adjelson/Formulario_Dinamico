<?php require_once APPROOT . '/app/views/layout/header.php'; ?>

<div class="auth-wrapper">
    <div class="auth-card">
        <h1 class="auth-title">Bem-vindo</h1>
        <p class="auth-subtitle">Inicie sessão para aceder à plataforma</p>

        <?php if (!empty($data['redirect'] ?? '')): ?>
            <div class="df-alert df-alert-info">
                <i class="fa-solid fa-circle-info"></i>
                <span>Após entrar será redirecionado para o formulário.</span>
            </div>
        <?php endif; ?>

        <form action="<?php echo URLROOT; ?>/login" method="POST" novalidate id="loginForm">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($data['redirect'] ?? ''); ?>">

            <div class="mb-3">
                <label class="form-label"><i class="fa-solid fa-envelope"></i> Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-at"></i></span>
                    <input type="email" name="email" class="form-control <?php echo !empty($data['email_err'] ?? '') ? 'is-invalid' : ''; ?>"
                        value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>"
                        placeholder="o.seu@email.com" autocomplete="email">
                </div>
                <?php if (!empty($data['email_err'] ?? '')): ?>
                    <div class="invalid-feedback d-flex"><i class="fa-solid fa-triangle-exclamation"></i><?php echo htmlspecialchars($data['email_err']); ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label class="form-label"><i class="fa-solid fa-lock"></i> Palavra Passe</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                    <input type="password" name="password" id="loginPw" class="form-control <?php echo !empty($data['password_err'] ?? '') ? 'is-invalid' : ''; ?>"
                        placeholder="••••••••" autocomplete="current-password">
                    <button class="btn btn-secondary btn-sm" type="button" onclick="togglePw('loginPw',this)" title="Mostrar/Ocultar">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <?php if (!empty($data['password_err'] ?? '')): ?>
                    <div class="invalid-feedback d-flex"><i class="fa-solid fa-triangle-exclamation"></i><?php echo htmlspecialchars($data['password_err']); ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fa-solid fa-right-to-bracket"></i> Entrar
            </button>
        </form>

        <div class="auth-divider"><span>ou</span></div>
        <p class="text-center mb-0" style="font-size:.875rem;">
            Não tem conta?
            <a href="<?php echo URLROOT; ?>/register<?php echo !empty($data['redirect'] ?? '') ? '?redirect=' . urlencode($data['redirect']) : ''; ?>" class="fw-600 text-success">
                Registar-se <i class="fa-solid fa-arrow-right fa-xs"></i>
            </a>
        </p>
    </div>
</div>

<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye','fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash','fa-eye');
    }
}
</script>
<?php require_once APPROOT . '/app/views/layout/footer.php'; ?>
