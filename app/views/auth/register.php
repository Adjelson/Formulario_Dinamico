<?php require APPROOT . '/app/views/layout/header.php'; ?>
<div class="auth-wrapper">
    <div class="auth-card" aria-labelledby="register-title">
        <h1 class="auth-title" id="register-title">Criar conta</h1>
        <p class="auth-subtitle">Preencha os dados para aceder aos formulários.</p>

        <?php if (!empty($data['general_err'])): ?><div class="alert alert-danger" role="alert"><?php echo e($data['general_err']); ?></div><?php endif; ?>

        <form action="<?php echo e(URLROOT); ?>/register" method="post">
<?php echo csrf_field(); ?>
<input type="hidden" name="redirect" value="<?php echo e($data['redirect'] ?? ''); ?>">

            <div class="mb-3">
                <label class="form-label" for="register-name">Nome completo</label>
                <input id="register-name" type="text" name="name" required maxlength="100" autocomplete="name"
                    class="form-control <?php echo !empty($data['name_err']) ? 'is-invalid' : ''; ?>" value="<?php echo e($data['name'] ?? ''); ?>">
                <?php if (!empty($data['name_err'])): ?><div class="invalid-feedback"><?php echo e($data['name_err']); ?></div><?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="register-email">Email</label>
                <input id="register-email" type="email" name="email" required maxlength="190" autocomplete="email"
                    class="form-control <?php echo !empty($data['email_err']) ? 'is-invalid' : ''; ?>" value="<?php echo e($data['email'] ?? ''); ?>">
                <?php if (!empty($data['email_err'])): ?><div class="invalid-feedback"><?php echo e($data['email_err']); ?></div><?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="register-password">Palavra-passe</label>
                <input id="register-password" type="password" name="password" required minlength="8" autocomplete="new-password"
                    class="form-control <?php echo !empty($data['password_err']) ? 'is-invalid' : ''; ?>" aria-describedby="password-help">
                <div class="form-text" id="password-help">Pelo menos 8 caracteres, com maiúscula, minúscula e número.</div>
                <?php if (!empty($data['password_err'])): ?><div class="invalid-feedback"><?php echo e($data['password_err']); ?></div><?php endif; ?>
            </div>

            <div class="mb-4">
                <label class="form-label" for="register-confirm">Confirmar palavra-passe</label>
                <input id="register-confirm" type="password" name="confirm_password" required autocomplete="new-password"
                    class="form-control <?php echo !empty($data['confirm_password_err']) ? 'is-invalid' : ''; ?>">
                <?php if (!empty($data['confirm_password_err'])): ?><div class="invalid-feedback"><?php echo e($data['confirm_password_err']); ?></div><?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-user-plus" aria-hidden="true"></i> Criar conta</button>
        </form>
        <div class="auth-divider"><span>ou</span></div>
        <p class="text-center mb-0">Já tem conta? <a href="<?php echo e(URLROOT); ?>/login">Entrar</a></p>
    </div>
</div>
<?php require APPROOT . '/app/views/layout/footer.php'; ?>
