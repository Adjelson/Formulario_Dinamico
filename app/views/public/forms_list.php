<?php require_once APPROOT . '/app/views/layout/header.php'; ?>
<div class="container-xl">
    <div class="page-header">
        <h1><i class="fa-solid fa-list-check"></i> Formulários Disponíveis</h1>
        <a href="<?php echo URLROOT; ?>/my/history" class="btn btn-secondary">
            <i class="fa-solid fa-clock-rotate-left"></i> Meu Histórico
        </a>
    </div>

    <div class="row mb-3">
        <div class="col-md-5">
            <div class="df-search">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="searchForms" placeholder="Pesquisar formulários..." class="form-control">
            </div>
        </div>
    </div>

    <?php if (empty($data['forms'])): ?>
        <div class="empty-state">
            <i class="fa-solid fa-folder-open"></i>
            <h5>Nenhum formulário disponível</h5>
            <p>Não existem formulários publicados de momento.</p>
        </div>
    <?php else: ?>
        <div class="forms-grid" id="formsGrid">
            <?php foreach ($data['forms'] as $form): ?>
            <div class="form-card" data-title="<?php echo strtolower(htmlspecialchars($form->title)); ?>">

                <!-- Imagem de capa ou gradiente fallback -->
                <?php if (!empty($form->cover_image)): ?>
                    <div class="form-card-cover">
                        <img src="<?php echo URLROOT . '/cover/' . urlencode($form->cover_image); ?>"
                             alt="<?php echo htmlspecialchars($form->title); ?>"
                             class="form-card-cover-img">
                    </div>
                <?php else: ?>
                    <div class="form-card-accent"></div>
                <?php endif; ?>

                <div class="form-card-body">
                    <h5 class="form-card-title"><?php echo htmlspecialchars($form->title); ?></h5>
                    <?php if (!empty($form->description)): ?>
                        <p class="form-card-desc"><?php echo htmlspecialchars($form->description); ?></p>
                    <?php else: ?>
                        <p class="form-card-desc text-muted fst-italic">Sem descrição.</p>
                    <?php endif; ?>
                </div>
                <div class="form-card-footer">
                    <a href="<?php echo URLROOT; ?>/forms/<?php echo htmlspecialchars($form->slug); ?>" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-arrow-right"></i> Preencher
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('searchForms')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#formsGrid .form-card').forEach(card => {
        card.style.display = card.dataset.title.includes(q) ? '' : 'none';
    });
});
</script>
<?php require_once APPROOT . '/app/views/layout/footer.php'; ?>
