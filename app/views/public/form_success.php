<?php require_once APPROOT . '/app/views/layout/header.php'; ?>
<div class="container" style="max-width:540px; padding-top:3rem;">
    <div class="df-card text-center p-5">
        <div style="width:72px;height:72px;background:var(--green-50);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;border:2px solid var(--green-200);">
            <i class="fa-solid fa-check-circle text-success" style="font-size:2rem;"></i>
        </div>
        <h2 class="fw-800 mb-2">Resposta Enviada!</h2>
        <p class="text-muted mb-4">
            <?php if ($data['form']): ?>
                A sua resposta ao formulário <strong><?php echo htmlspecialchars($data['form']->title); ?></strong> foi registada com sucesso.
            <?php else: ?>
                A sua resposta foi registada com sucesso.
            <?php endif; ?>
        </p>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
            <?php if (!empty($data['slug'])): ?>
                <a href="<?php echo URLROOT; ?>/forms/<?php echo htmlspecialchars($data['slug']); ?>" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-rotate-right"></i> Submeter outra
                </a>
            <?php endif; ?>
            <a href="<?php echo URLROOT; ?>/my/history" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-clock-rotate-left"></i> Ver Histórico
            </a>
            <a href="<?php echo URLROOT; ?>/home" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-home"></i> Início
            </a>
        </div>
    </div>
</div>
<?php require_once APPROOT . '/app/views/layout/footer.php'; ?>
