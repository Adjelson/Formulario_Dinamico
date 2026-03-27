<?php require_once APPROOT . '/app/views/layout/header.php'; ?>

<div class="auth-wrapper">
    <div class="auth-card" style="max-width:480px;">
        <div class="auth-logo"><i class="fa-solid fa-user-plus"></i></div>
        <h1 class="auth-title">Criar Conta</h1>
        <p class="auth-subtitle">Preencha os dados para se registar</p>

        <?php if (!empty($data['redirect'] ?? '')): ?>
            <div class="df-alert df-alert-info">
                <i class="fa-solid fa-circle-info"></i>
                <span>Após criar a conta será redirecionado para o formulário.</span>
            </div>
        <?php endif; ?>

        <form action="<?php echo URLROOT; ?>/register" method="POST" novalidate>
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($data['redirect'] ?? ''); ?>">

            <div class="mb-3">
                <label class="form-label"><i class="fa-solid fa-user"></i> Nome completo</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-id-card"></i></span>
                    <input type="text" name="name" class="form-control <?php echo !empty($data['name_err'] ?? '') ? 'is-invalid' : ''; ?>"
                        value="<?php echo htmlspecialchars($data['name'] ?? ''); ?>"
                        placeholder="O seu nome completo" required>
                </div>
                <?php if (!empty($data['name_err'] ?? '')): ?>
                    <div class="invalid-feedback d-flex"><i class="fa-solid fa-triangle-exclamation"></i><?php echo htmlspecialchars($data['name_err']); ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="fa-solid fa-envelope"></i> Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-at"></i></span>
                    <input type="email" name="email" class="form-control <?php echo !empty($data['email_err'] ?? '') ? 'is-invalid' : ''; ?>"
                        value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>"
                        placeholder="o.seu@email.com" required>
                </div>
                <?php if (!empty($data['email_err'] ?? '')): ?>
                    <div class="invalid-feedback d-flex"><i class="fa-solid fa-triangle-exclamation"></i><?php echo htmlspecialchars($data['email_err']); ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label"><i class="fa-solid fa-lock"></i> Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                    <input type="password" name="password" id="regPw" class="form-control <?php echo !empty($data['password_err'] ?? '') ? 'is-invalid' : ''; ?>"
                        placeholder="Mínimo 6 caracteres" required>
                    <button class="btn btn-secondary btn-sm" type="button" onclick="togglePw('regPw',this)"><i class="fa-solid fa-eye"></i></button>
                </div>
                <div class="form-text"><i class="fa-solid fa-circle-info me-1"></i>Mínimo de 6 caracteres.</div>
                <?php if (!empty($data['password_err'] ?? '')): ?>
                    <div class="invalid-feedback d-flex"><i class="fa-solid fa-triangle-exclamation"></i><?php echo htmlspecialchars($data['password_err']); ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label class="form-label"><i class="fa-solid fa-lock"></i> Confirmar Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-check"></i></span>
                    <input type="password" name="confirm_password" id="regPw2" class="form-control <?php echo !empty($data['confirm_password_err'] ?? '') ? 'is-invalid' : ''; ?>"
                        placeholder="Repita a password" required>
                    <button class="btn btn-secondary btn-sm" type="button" onclick="togglePw('regPw2',this)"><i class="fa-solid fa-eye"></i></button>
                </div>
                <?php if (!empty($data['confirm_password_err'] ?? '')): ?>
                    <div class="invalid-feedback d-flex"><i class="fa-solid fa-triangle-exclamation"></i><?php echo htmlspecialchars($data['confirm_password_err']); ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fa-solid fa-user-plus"></i> Criar Conta
            </button>
        </form>

        <div class="auth-divider"><span>ou</span></div>
        <p class="text-center mb-0" style="font-size:.875rem;">
            Já tem conta?
            <a href="<?php echo URLROOT; ?>/login<?php echo !empty($data['redirect'] ?? '') ? '?redirect=' . urlencode($data['redirect']) : ''; ?>" class="fw-600 text-success">
                Entrar <i class="fa-solid fa-arrow-right fa-xs"></i>
            </a>
        </p>
    </div>
</div>
<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') { input.type = 'text'; icon.classList.replace('fa-eye','fa-eye-slash'); }
    else { input.type = 'password'; icon.classList.replace('fa-eye-slash','fa-eye'); }
}
</script>
<?php require_once APPROOT . '/app/views/layout/footer.php'; ?>
